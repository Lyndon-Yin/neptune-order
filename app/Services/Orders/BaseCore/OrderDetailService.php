<?php
namespace App\Services\Orders\BaseCore;

/**
 * Class OrderDetailService
 * @package App\Services\Orders\BaseCore
 */
class OrderDetailService extends BaseOrderService
{
    /**
     * OrderDetailService constructor.
     * @param string $orderId
     * @throws \Exception
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;

        $this->initOrderInfo();
    }

    /**
     * @throws \Exception
     */
    protected function initOrderInfo()
    {
        // 获取订单主表信息
        $info = $this->orderRepo->getRepoRowByPrimaryKey(intval($this->orderId));
        if (empty($info)) {
            throw new \Exception('未识别订单信息');
        }

        $this->merchantId     = $info['merchant_id'];
        $this->userId         = $info['user_id'];
        $this->orderCategory  = $info['order_category'];
        $this->orderSource    = $info['order_source'];
        $this->totalAmount    = $info['total_amount'];
        $this->discountAmount = $info['discount_amount'];
        $this->shippingAmount = $info['shipping_amount'];
        $this->paymentAmount  = $info['payment_amount'];
        $this->deliveryType   = $info['delivery_type'];
        $this->orderStatus    = $info['order_status'];
        $this->orderRemark    = $info['order_remark'];
        $this->orderTime      = $info['order_time'];
        $this->createdAt      = $info['created_at'];
    }

    /**
     * 重写BaseOrderService中getOrderItems方法
     * 订单详情首次访问进行数据库查询
     *
     * @return array
     */
    public function getOrderItems()
    {
        if (empty($this->orderItems)) {
            $this->orderItems = $this->orderItemRepo->getOrderItemsByOrderId($this->orderId);
        }

        return $this->orderItems;
    }
}
