<?php
namespace App\Services\Orders\GroupOrder;


use App\innerApi\GoodsAppApi;
use App\Services\Orders\BaseCore\OrderCreateService;

/**
 * Class CreateGroupOrderService
 * @package App\Services\Orders\NormalOrder
 */
class CreateGroupOrderService extends OrderCreateService
{
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

        // 调用父类构造函数
        parent::__construct($alphaGroup['merchant_id'], $userId, $buyEntities);

        // 类属性赋值
        $this->alphaId      = $alphaId;
        $this->alphaGroupId = $alphaGroupId;
        $this->alphaBatchId = $alphaBatchId;
        $this->alphaGroup   = $alphaGroup;
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
    }

    /**
     * 获取购买的实体信息
     *
     * @return array
     * @throws \Exception
     */
    protected function getEntityByEntityIds()
    {
        // 远程获取实体信息
        $data = (GoodsAppApi::getInstance())->getEntityByEntityIds(
            $this->alphaId,
            $this->alphaGroupId,
            $this->alphaBatchId,
            array_values($this->buyEntities)
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
                'id'             => $val['id'],
                'goods_id'       => $val['goods_info']['id'],
                'sell_price'     => $val['sell_price'],
                'goods_name'     => $val['goods_info']['goods_name'],
                'entity_image'   => $val['entity_image'],
                'spec_name_json' => $val['spec_name_json'],
                'quantity'       => $val['quantity']
            ];
        }, $data);
    }
}
