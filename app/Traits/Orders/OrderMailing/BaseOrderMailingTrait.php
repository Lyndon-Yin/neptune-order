<?php
namespace App\Traits\Orders\OrderMailing;


use App\Repositories\Orders\OrderRepository;
use App\Repositories\Orders\OrderMailingRepository;

/**
 * Trait BaseOrderMailingTrait
 * @package App\Traits\Orders\OrderMailing
 *
 * @property OrderRepository $orderRepo
 * @property OrderMailingRepository $orderMailRepo
 */
trait BaseOrderMailingTrait
{
    // 收货人姓名
    protected $consigneeName = '';

    public function getConsigneeName()
    {
        return $this->consigneeName;
    }

    // 收货人联系方式
    protected $consigneePhone = '';

    public function getConsigneePhone()
    {
        return $this->consigneePhone;
    }

    // 收货人地址
    protected $shippingAddress = '';

    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    // 发货快递单号
    protected $shippingNo = '';

    public function getShippingNo()
    {
        return $this->shippingNo;
    }

    // 发货时间
    protected $shippingTime = null;

    public function getShippingTime()
    {
        return $this->shippingTime;
    }
}
