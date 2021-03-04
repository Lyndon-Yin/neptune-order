<?php
namespace App\Traits\Orders\OrderMailing;

/**
 * Trait DetailOrderMailingTrait
 * @package App\Traits\Orders\OrderMailing
 */
trait DetailOrderMailingTrait
{
    use BaseOrderMailingTrait;

    /**
     * @var bool 是否已经查询过邮寄信息表
     */
    protected $hasQueryMailingTable = false;

    /**
     * 获取配送信息数组
     *
     * @return array
     */
    public function getOrderMailingArray()
    {
        $this->initMailingInfo();

        return [
            'consignee_name'   => $this->consigneeName,
            'consignee_phone'  => $this->consigneePhone,
            'shipping_address' => $this->shippingAddress,
            'point_lng'        => $this->pointLng,
            'point_lat'        => $this->pointLat,
            'shipping_no'      => $this->shippingNo,
            'shipping_time'    => $this->shippingTime
        ];
    }

    public function getConsigneeName()
    {
        $this->initMailingInfo();

        return $this->consigneeName;
    }

    public function getConsigneePhone()
    {
        $this->initMailingInfo();

        return $this->consigneePhone;
    }

    public function getShippingAddress()
    {
        $this->initMailingInfo();

        return $this->shippingAddress;
    }

    public function getPointLat()
    {
        $this->initMailingInfo();

        return $this->pointLat;
    }

    public function getPointLng()
    {
        $this->initMailingInfo();

        return $this->pointLng;
    }

    public function getShippingNo()
    {
        $this->initMailingInfo();

        return $this->shippingNo;
    }

    public function getShippingTime()
    {
        $this->initMailingInfo();

        return $this->shippingTime;
    }

    /**
     * 初始化订单邮寄信息
     */
    private function initMailingInfo()
    {
        if (empty($this->orderId)) {
            return;
        }
        if ($this->hasQueryMailingTable) {
            return;
        }

        // 数据库od_order_mailing查询
        $info = $this->orderMailRepo->getRepoRowByPrimaryKey($this->orderId);
        $this->hasQueryMailingTable = true;
        if (empty($info)) {
            return;
        }

        $this->consigneeName   = $info['consignee_name'];
        $this->consigneePhone  = $info['consignee_phone'];
        $this->shippingAddress = $info['shipping_address'];
        $this->shippingNo      = $info['shipping_no'];
        $this->shippingTime    = $info['shipping_time'];
        $this->pointLng        = $info['point_lng'];
        $this->pointLat        = $info['point_lat'];
    }
}
