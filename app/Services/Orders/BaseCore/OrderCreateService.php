<?php
namespace App\Services\Orders\BaseCore;


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
            // 只有购买的"商品价格"和"数量"为必传值，其他值均可为空
            if (empty($val['buy_quantity']) || empty($val['entity_price'])) {
                throw new \Exception('购买的商品数量或价格不能为空');
            }

            $val['buy_quantity'] = intval($val['buy_quantity']);
            if ($val['buy_quantity'] < 1) {
                throw new \Exception('购买的商品数量必须大于1');
            }
            $val['entity_price'] = round($val['entity_price'], 2);
            if ($val['entity_price'] < 0.01) {
                throw new \Exception('购买的商品价格不能小于0.01');
            }

            $this->buyEntities[] = [
                'goods_id'          => empty($val['goods_id']) ? 0 : intval($val['goods_id']),
                'entity_id'         => empty($val['entity_id']) ? 0 : intval($val['entity_id']),
                'goods_name'        => empty($val['goods_name']) ? '' : trim($val['goods_name']),
                'entity_img'        => empty($val['entity_img']) ? '' : trim($val['entity_img']),
                'entity_price'      => $val['entity_price'],
                'entity_spec_value' => empty($val['entity_spec_value']) ? '' : trim($val['entity_spec_value']),
                'buy_quantity'      => $val['buy_quantity']
            ];
        }
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function createOrder()
    {
        // 验证购买的商品不能为空
        if (empty($this->buyEntities)) {
            throw new \Exception('购买的商品不能为空');
        }

        // 初始化订单ID
        $this->orderId = order_id();

        $this->beforeTransaction();

        try {
            // 开启事务
            DB::beginTransaction();

            // 订单详情表od_order_items添加
            $this->createOrderItemsTable();

            // 订单主表od_orders添加
            $this->createOrderTable();

            $results = $this->doTransaction();

            // 事务提交
            DB::commit();
        } catch (\Exception $e) {
            // 事务回滚
            DB::rollBack();

            $this->afterExceptionTransaction();

            throw new \Exception($e->getMessage(), $e->getCode());
        }

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
     * 事务成功后置操作方法
     */
    protected function afterTransaction()
    {

    }

    /**
     * 事务异常后置操作方法
     */
    protected function afterExceptionTransaction()
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

        // 支付金额等于总金额减去折扣金额
        $this->paymentAmount = $this->totalAmount - $this->discountAmount;

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
        if (empty($this->buyEntities)) {
            return;
        }

        foreach ($this->buyEntities as $entity) {
            $totalAmount = round($entity['buy_quantity'] * $entity['entity_price'], 2);
            $this->orderItems[] = [
                'order_id'             => $this->orderId,
                'goods_id'             => $entity['goods_id'],
                'entity_id'            => $entity['entity_id'],
                'goods_name'           => $entity['goods_name'],
                'entity_img'           => $entity['entity_img'],
                'entity_price'         => $entity['entity_price'],
                'entity_spec_value'    => $entity['entity_spec_value'],
                'buy_quantity'         => $entity['buy_quantity'],
                'item_total_amount'    => $totalAmount,
                'item_discount_amount' => 0,
                'item_payment_amount'  => $totalAmount
            ];

            // 累计订单总金额
            $this->totalAmount += $totalAmount;
        }

        // 总金额加上配送费用
        $this->totalAmount += $this->shippingAmount;

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
        // 填充订单详情表
        $this->fillGoodsInfoForOrderItems();

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
            'order_remark'    => $this->orderRemark,
            'order_time'      => $this->orderTime
        ];

        return $this->orderRepo->addRepoRow($temp);
    }
}
