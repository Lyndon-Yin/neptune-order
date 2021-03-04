<?php
namespace App\Services\Orders\GroupOrder;


use App\innerApi\GoodsAppApi;
use App\innerApi\MemberAppApi;
use App\Models\Orders\OrderModel;
use Illuminate\Support\Facades\DB;
use App\Services\Orders\BaseCore\OrderCreateService;
use App\Traits\Orders\OrderMailing\CreateOrderMailingTrait;

/**
 * Class CreateGroupOrderService
 * @package App\Services\Orders\NormalOrder
 */
class CreateGroupOrderService extends OrderCreateService
{
    use CreateOrderMailingTrait;

    /**
     * @var int 团长ID
     */
    public $alphaId = 0;

    /**
     * @var int 团长开团ID
     */
    public $alphaGroupId = 0;

    /**
     * @var int 开团批次ID
     */
    public $alphaBatchId = 0;

    /**
     * @var array 团长开团信息
     */
    public $alphaGroup = [];

    /**
     * CreateGroupOrderService constructor.
     * @param int $alphaId
     * @param int $alphaGroupId
     * @param int $alphaBatchId
     * @param int $userId
     * @param array $buyEntities
     * @throws \Exception
     */
    public function __construct($alphaId, $alphaGroupId, $alphaBatchId, $userId, $buyEntities = [])
    {
        // 获取团长开团信息
        $alphaGroup = (GoodsAppApi::getInstance())
            ->getAlphaGroupInfo($alphaId, $alphaGroupId, $alphaBatchId);
        if (empty($alphaGroup)) {
            throw new \Exception('未识别团购信息');
        }
        // 验证是否到期
        if (strtotime($alphaGroup['end_datetime']) < time() - 60) {
            throw new \Exception('你来晚了，已截团!');
        }
        if (strtotime($alphaGroup['batch']['end_datetime']) < time() - 60) {
            $tmp = $alphaGroup['batch']['arrival_date'] . ' ' . $alphaGroup['batch']['arrival_time'];
            throw new \Exception('到货时间为：' . $tmp . '批次，已截团');
        }

        // 类属性赋值
        $this->alphaId      = $alphaId;
        $this->alphaGroupId = $alphaGroupId;
        $this->alphaBatchId = $alphaBatchId;
        $this->alphaGroup   = $alphaGroup;

        // 获取购买的商品信息列表
        $buyEntities = array_filter($buyEntities, function ($val) {
            if (empty($val['quantity']) || empty($val['entity_id'])) {
                return false;
            }

            $quantity = intval($val['quantity']);
            if ($quantity <= 0) {
                return false;
            }

            return true;
        });
        $buyEntities = $this->getEntityByEntityIds($buyEntities);

        // 调用父类构造函数
        parent::__construct($alphaGroup['merchant_id'], $userId, $buyEntities);
    }

    /**
     * @throws \Exception
     */
    protected function beforeTransaction()
    {
        parent::beforeTransaction();

        // 验证配送方式
        $this->parseDeliveryType();
    }

    /**
     * 订单创建具体内容
     *
     * @return mixed|null|void
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    protected function doTransaction()
    {
        parent::doTransaction();

        // 订单团购分组表od_order_group_buy添加
        $tmp = [
            'order_id'         => $this->orderId,
            'group_id'         => $this->alphaGroup['group_id'],
            'group_batch_id'   => $this->alphaGroup['batch']['group_batch_id'],
            'alpha_id'         => $this->alphaId,
            'alpha_group_id'   => $this->alphaGroupId,
            'alpha_batch_id'   => $this->alphaBatchId,
            'alpha_group_name' => $this->alphaGroup['group_name'],
            'arrival_date'     => $this->alphaGroup['batch']['arrival_date'],
            'arrival_time'     => $this->alphaGroup['batch']['arrival_time']
        ];
        $this->orderGroupBuyRepo->addRepoRow($tmp);

        // 添加邮寄信息od_order_mailing
        $this->createOrderMailingTable();

        // od_order_mailing_home表添加
        if ($this->deliveryType == OrderModel::DELIVERY_FETCH_HOME) {
            $tmp = $this->getUserAddressInfo();

            $tmp['order_id'] = $this->orderId;
            $tmp['point']    = DB::raw("GeomFromText('POINT(" . $tmp['point_lng']. " " . $tmp['point_lat'] . ")')");

            $this->orderMailHomeRepo->addRepoRow($tmp);
        }

        /** 注意：商品扣除库存需要放在最后。否则存在库存扣除成功，订单创建失败的可能 **/
        // 不用$this->buyEntities数据，是因为该数据为原始传参数据，正确性未验证
        $tmp = array_map(function ($val) {
            return [
                'entity_id' => $val['entity_id'],
                'quantity'  => $val['buy_quantity']
            ];
        }, $this->orderItems);
        (GoodsAppApi::getInstance())->orderConsumeStock(
            $this->alphaGroup['merchant_id'],
            $this->alphaGroup['group_id'],
            $this->alphaGroup['batch']['group_batch_id'],
            $tmp,
            $this->orderId
        );
    }

    /**
     * 解析配送类型，并进行参数验证
     *
     * @throws \Exception
     */
    protected function parseDeliveryType()
    {
        switch ($this->deliveryType) {
            case OrderModel::DELIVERY_MAILING:
                // 快递到家，验证团购是否支持快递到家
                if ($this->alphaGroup['delivery_type'] != 1) {
                    throw new \Exception('该团购不支持快递到家');
                }

                // 推入邮寄地址
                $tmp = $this->getUserAddressInfo();
                $this->pushMailingInfo($tmp);

                break;
            case OrderModel::DELIVERY_FETCH:
                // 快递到自提点，验证团购是否支持快递到自提点
                if ($this->alphaGroup['delivery_type'] != 2) {
                    throw new \Exception('该团购不支持站点自提');
                }
                if (empty($this->alphaGroup['position'])) {
                    throw new \Exception('团购异常，该团购无自提地址');
                }

                // 推入邮寄地址
                $pos = $this->alphaGroup['position'];
                $tmp = [
                    'consignee_name'   => $pos['pickup_name'],
                    'consignee_phone'  => $pos['pickup_mobile'],
                    'shipping_address' => $pos['pickup_address'],
                    'point_lng'        => $pos['lng'],
                    'point_lat'        => $pos['lat']
                ];
                $this->pushMailingInfo($tmp);

                break;
            case OrderModel::DELIVERY_FETCH_HOME:
                // 快递到自提点，然后团长配送到家
                if ($this->alphaGroup['delivery_type'] != 2) {
                    throw new \Exception('该团购不支持团长配送到家');
                }
                if (empty($this->alphaGroup['position'])) {
                    throw new \Exception('团购异常，该团购无自提地址');
                }

                $pos = $this->alphaGroup['position'];
                if ($pos['delivery_home'] != 'yes') {
                    throw new \Exception('该团购无团长配送到家服务');
                }

                // 验证是否在配送范围内
                $tmp = $this->getUserAddressInfo();
                $distance = distance($pos['lng'], $pos['lat'], $tmp['point_lng'], $tmp['point_lat']);
                if ($distance > $pos['radius']) {
                    throw new \Exception('超过配送范围，无法配送到家');
                }

                // 推入邮寄地址
                $tmp = [
                    'consignee_name'   => $pos['pickup_name'],
                    'consignee_phone'  => $pos['pickup_mobile'],
                    'shipping_address' => $pos['pickup_address'],
                    'point_lng'        => $pos['lng'],
                    'point_lat'        => $pos['lat']
                ];
                $this->pushMailingInfo($tmp);

                break;
            default:
                throw new \Exception('未识别订单配送方式');
        }
    }

    /**
     * 获取用户邮寄地址
     *
     * @return array
     * @throws \Exception
     */
    protected function getUserAddressInfo()
    {
        static $address = [];
        if (! empty($address)) {
            return $address;
        }

        $data = (MemberAppApi::getInstance())->getUserAddressInfo(
            $this->userId,
            $this->userAddressId
        );
        if (empty($data)) {
            throw new \Exception('未识别用户收获地址ID');
        }

        $address = [
            'consignee_name'   => $data['username'],
            'consignee_phone'  => $data['mobile'],
            'shipping_address' => $data['detail_address'],
            'point_lng'        => $data['lng'],
            'point_lat'        => $data['lat']
        ];
        return $address;
    }

    /**
     * 获取购买的实体信息
     *
     * @param array $buyEntities
     * @return array
     * @throws \Exception
     */
    protected function getEntityByEntityIds($buyEntities)
    {
        // 远程获取实体信息
        $data = (GoodsAppApi::getInstance())->getEntityByEntityIds(
            $this->alphaGroup['merchant_id'],
            $this->alphaGroup['group_id'],
            $this->alphaGroup['batch']['group_batch_id'],
            array_values($buyEntities)
        );

        // 验证库存数量
        $stockError = [];
        foreach ($data as $val) {
            if ($val['stock_num'] < $val['quantity']) {
                // 截取商品部分名称，防止商品名称过长
                $tmp = $val['goods_info']['goods_name'];
                if (strlen($tmp) > 20) {
                    $tmp = mb_substr($tmp, 0, 20) . '...';
                }

                // 错误提示
                $stockError[] = '商品：' . $tmp . '库存不足，剩余库存：' . $val['stock_num'];
            }
        }
        if (! empty($stockError)) {
            throw new \Exception(implode(';', $stockError));
        }

        // 结果处理
        return array_map(function ($val) {
            return [
                'entity_id'         => $val['id'],
                'goods_id'          => $val['goods_info']['id'],
                'entity_price'      => $val['sell_price'],
                'goods_name'        => $val['goods_info']['goods_name'],
                'entity_img'        => $val['entity_image'],
                'entity_spec_value' => $val['spec_name_json'],
                'buy_quantity'      => $val['quantity']
            ];
        }, $data);
    }
}
