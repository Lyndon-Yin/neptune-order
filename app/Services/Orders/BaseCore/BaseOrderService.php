<?php
namespace App\Services\Orders\BaseCore;


use App\Services\BaseService;
use App\Traits\RepositoryLazyLoad\RepositoryLazyLoadOrder;

/**
 * Class BaseOrderService
 * @package App\Services\Orders\BaseCore
 */
class BaseOrderService extends BaseService
{
    use RepositoryLazyLoadOrder;

    /** 订单主表od_orders字段 **/
    // 订单ID
    protected $orderId = 0;

    public function getOrderId()
    {
        return $this->orderId;
    }

    // 商家ID
    protected $merchantId = 0;

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    // 用户ID
    protected $userId = 0;

    public function getUserId()
    {
        return $this->userId;
    }

    // 订单分类
    protected $orderCategory = 0;

    public function getOrderCategory()
    {
        return $this->orderCategory;
    }

    public function pushOrderCategory(int $orderCategory)
    {
        $this->orderCategory = $orderCategory;

        return $this;
    }

    // 订单来源
    protected $orderSource = 0;

    public function getOrderSource()
    {
        return $this->orderSource;
    }

    public function pushOrderSource(int $orderSource)
    {
        $this->orderSource = $orderSource;

        return $this;
    }

    // 订单总金额
    protected $totalAmount = 0.00;

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    // 订单总折扣金额
    protected $discountAmount = 0.00;

    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    public function pushDiscountAmount(float $discountAmount)
    {
        $this->discountAmount = round($discountAmount, 2);

        return $this;
    }

    // 运费
    protected $shippingAmount = 0.00;

    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    public function pushShippingAmount(float $shippingAmount)
    {
        $this->shippingAmount = round($shippingAmount, 2);

        return $this;
    }

    // 实付金额
    protected $paymentAmount = 0.00;

    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    // 订单配送类型
    protected $deliveryType = 0;

    public function getDeliveryType()
    {
        return $this->deliveryType;
    }

    public function pushDeliveryType(int $deliveryType)
    {
        $this->deliveryType = intval($deliveryType);

        return $this;
    }

    // 订单状态
    protected $orderStatus = 0;

    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    // 订单备注
    protected $orderRemark = '';

    public function getOrderRemark()
    {
        return $this->orderRemark;
    }

    public function pushOrderRemark($orderRemark)
    {
        $this->orderRemark = trim($orderRemark);

        return $this;
    }

    // 订单完成时间
    protected $orderTime = null;

    public function getOrderTime()
    {
        return $this->orderTime;
    }

    // 订单创建时间
    protected $createdAt = null;

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /** 订单详情表od_order_items字段 **/
    protected $orderItems = [];

    public function getOrderItems()
    {
        return $this->orderItems;
    }
}
