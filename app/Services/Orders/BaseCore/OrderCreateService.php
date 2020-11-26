<?php
namespace App\Services\Orders\BaseCore;


use App\innerApi\GoodsAppApi;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderCreateService
 * @package App\Services\Orders\BaseCore
 */
class OrderCreateService extends BaseOrderService
{
    // 购买的商品实体列表
    protected $buyEntities = [];

    /**
     * OrderCreateService constructor.
     * @param int $merchantId
     * @param int $userId
     * @param array $buyEntities
     * @throws \Exception
     */
    public function __construct(int $merchantId, int $userId, array $buyEntities = [])
    {
        $this->merchantId = intval($merchantId);
        if ($this->merchantId <= 0) {
            throw new \Exception('商家ID不能为空');
        }

        $this->userId = intval($userId);
        if ($this->userId <= 0) {
            throw new \Exception('用户ID不能为空');
        }

        foreach ($buyEntities as $val) {
            if (empty($val['quantity']) || empty($val['entity_id'])) {
                continue;
            }

            $quantity = intval($val['quantity']);
            $entityId = intval($val['entity_id']);
            if ($quantity <= 0 || $entityId <= 0) {
                continue;
            }

            if (isset($this->buyEntities[$entityId])) {
                $this->buyEntities[$entityId] += $quantity;
            } else {
                $this->buyEntities[$entityId] = $quantity;
            }
        }
    }

    /**
     * 添加配送类型，快递/自提等
     *
     * @param int $deliveryType
     * @return $this
     */
    public function pushDeliveryType(int $deliveryType)
    {
        $this->deliveryType = intval($deliveryType);

        return $this;
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function createOrder()
    {
        // 验证必填参数
        if (empty($this->buyEntities)) {
            throw new \Exception('购买的商品不能为空');
        }

        // 初始化订单ID
        $this->orderId = order_id();
        // 根据传参实体填充订单详情表
        $this->fillGoodsInfoForOrderItems();

        $this->beforeTransaction();

        $results = DB::transaction(function () {
            $results = $this->doTransaction();

            // 订单详情表od_order_items添加
            $this->createOrderItemsTable();

            // 订单主表od_orders添加
            $this->createOrderTable();

            return $results;
        });

        $this->afterTransaction();

        return $results;
    }

    /**
     * @throws \Exception
     */
    protected function beforeTransaction()
    {

    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    protected function doTransaction()
    {

    }

    /**
     * @throws \Exception
     */
    protected function afterTransaction()
    {

    }

    /**
     * 计算每个商品共享的折扣价格
     *
     * @return bool
     * @throws \Exception
     */
    protected function sharedDiscountAmount()
    {
        if ($this->discountAmount < 0.01) {
            return true;
        }
        if ($this->discountAmount > $this->totalAmount) {
            throw new \Exception('折扣金额大于消费总金额');
        }

        // 计算每件商品分摊的折扣金额
        $rate  = $this->discountAmount / $this->totalAmount;
        $count = count($this->orderItems) - 1;

        $tmpDiscountAmount = 0;
        foreach ($this->orderItems as $key => &$item) {
            if ($count == $key) {
                // 最后一个商品，如果继续乘以折扣比例，可能出现价格偏差
                $item['item_discount_amount'] = round($this->discountAmount - $tmpDiscountAmount, 2);
            } else {
                $item['item_discount_amount'] = round($item['item_total_amount'] * $rate, 2);

                $tmpDiscountAmount += $item['item_discount_amount'];
            }

            $item['item_payment_amount'] = round($item['item_total_amount'] - $item['item_discount_amount'], 2);
        }
        unset($item, $tmpDiscountAmount);

        return true;
    }

    /**
     * 根据传参实体填充订单详情表
     * 订单总金额在此处累加而生成
     *
     * @throws \Exception
     */
    protected function fillGoodsInfoForOrderItems()
    {
        // 获取实体信息
        $entityList = (GoodsAppApi::getInstance())
            ->getEntityByEntityIds($this->merchantId, array_keys($this->buyEntities));
        if (empty($entityList)) {
            throw new \Exception('购买的商品不能为空');
        }

        foreach ($entityList as $entity) {
            if (! isset($this->buyEntities[$entity['id']])) {
                continue;
            }

            $totalAmount = round($this->buyEntities[$entity['id']] * $entity['sell_price'], 2);
            $this->orderItems[] = [
                'order_id'             => $this->orderId,
                'goods_id'             => $entity['goods_id'],
                'entity_id'            => $entity['id'],
                'goods_name'           => $entity['goods_name'],
                'entity_img'           => $entity['entity_image'],
                'entity_price'         => $entity['sell_price'],
                'entity_spec_value'    => $entity['spec_name_json'],
                'buy_quantity'         => $this->buyEntities[$entity['id']],
                'item_total_amount'    => $totalAmount,
                'item_discount_amount' => 0,
                'item_payment_amount'  => $totalAmount
            ];

            // 累计订单总金额
            $this->totalAmount += $totalAmount;
        }

        // 支付金额初始化等于订单金额
        $this->paymentAmount = $this->totalAmount;
    }

    /**
     * 订单详情表od_order_items添加
     *
     * @throws \Exception
     */
    protected function createOrderItemsTable()
    {
        // 如果存在折扣金额，每个商品共享该折扣金额
        $this->sharedDiscountAmount();

        // 订单详情表od_order_items添加
        $this->orderItemRepo->batchAddRepoList($this->orderItems);
    }

    /**
     * 订单主表od_orders添加
     *
     * @return mixed
     * @throws \Lyndon\Exceptions\ModelException
     */
    protected function createOrderTable()
    {
        // 计算支付价格
        $this->paymentAmount = $this->totalAmount - $this->discountAmount;

        $temp = [
            'id'              => $this->orderId,
            'merchant_id'     => $this->merchantId,
            'user_id'         => $this->userId,
            'order_category'  => $this->orderCategory,
            'order_source'    => $this->orderSource,
            'total_amount'    => $this->totalAmount,
            'discount_amount' => $this->discountAmount,
            'shipping_amount' => $this->shippingAmount,
            'payment_amount'  => $this->paymentAmount,
            'delivery_type'   => $this->deliveryType,
            'order_status'    => $this->orderStatus,
            'order_time'      => $this->orderTime
        ];

        return $this->orderRepo->addRepoRow($temp);
    }
}
