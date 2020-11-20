<?php
namespace App\Services\Orders\NormalOrder;


use App\Traits\Orders\BaseOrderMailingTrait;
use App\Services\Orders\BaseCore\OrderEditService;

/**
 * Class EditNormalOrderService
 * @package App\Services\Orders\NormalOrder
 */
class EditNormalOrderService extends OrderEditService
{
    use BaseOrderMailingTrait;

    private $hasQueryMailingTable = false;

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
        if ($this->hasQueryMailingTable) {
            return;
        }

        $info = $this->orderMailRepo->getRepoRowByPrimaryKey($this->orderId);
        $this->hasQueryMailingTable = true;

        if (empty($info)) {
            $this->consigneeName   = '';
            $this->consigneePhone  = '';
            $this->shippingAddress = '';
            $this->shippingNo      = '';
            $this->shippingTime    = null;
        } else {
            $this->consigneeName   = $info['consignee_name'];
            $this->consigneePhone  = $info['consignee_phone'];
            $this->shippingAddress = $info['shipping_address'];
            $this->shippingNo      = $info['shipping_no'];
            $this->shippingTime    = $info['shipping_time'];
        }
    }
}
