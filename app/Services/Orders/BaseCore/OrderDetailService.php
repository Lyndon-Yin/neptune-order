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
        $this->orderId = intval($orderId);

        $this->initOrderInfo();
    }

    /**
     * @throws \Exception
     */
    protected function initOrderInfo()
    {
        // 获取订单主表信息
        $info = $this->orderRepo->getRepoRowByPrimaryKey($this->orderId);
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
     * 获取订单主表信息
     *
     * @return array
     */
    public function getOrderArray()
    {
        return [
            'merchant_id'     => $this->merchantId,
            'user_id'         => $this->userId,
            'order_category'  => $this->orderCategory,
            'order_source'    => $this->orderSource,
            'total_amount'    => $this->totalAmount,
            'discount_amount' => $this->discountAmount,
            'shipping_amount' => $this->shippingAmount,
            'payment_amount'  => $this->shippingAmount,
            'delivery_type'   => $this->deliveryType,
            'order_status'    => $this->orderStatus,
            'order_remark'    => $this->orderRemark,
            'order_time'      => $this->orderTime,
            'created_at'      => $this->createdAt
        ];
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
