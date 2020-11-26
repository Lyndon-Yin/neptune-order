<?php
namespace App\Traits\Orders\OrderMailing;

/**
 * Trait DetailOrderMailingTrait
 * @package App\Traits\Orders\OrderMailing
 */
trait DetailOrderMailingTrait
{
    use BaseOrderMailingTrait;

    // 是否已经查询过邮寄信息表
    protected $hasQueryMailingTable = false;

    // 订单详情数组
    protected $orderMailingArray = [];

    /**
     * @return array
     */
    public function getOrderMailingArray()
    {
        $this->initMailingInfo();
        return $this->orderMailingArray;
    }

    /**
     * 重写BaseOrderMailingTrait中方法，首次获取进行数据库查询
     *
     * @return string
     */
    public function getConsigneeName()
    {
        $this->initMailingInfo();
        return $this->consigneeName;
    }

    /**
     * 重写BaseOrderMailingTrait中方法，首次获取进行数据库查询
     *
     * @return string
     */
    public function getConsigneePhone()
    {
        $this->initMailingInfo();
        return $this->consigneePhone;
    }

    /**
     * 重写BaseOrderMailingTrait中方法，首次获取进行数据库查询
     *
     * @return string
     */
    public function getShippingAddress()
    {
        $this->initMailingInfo();
        return $this->shippingAddress;
    }

    /**
     * 重写BaseOrderMailingTrait中方法，首次获取进行数据库查询
     *
     * @return string
     */
    public function getShippingNo()
    {
        $this->initMailingInfo();
        return $this->shippingNo;
    }

    /**
     * 重写BaseOrderMailingTrait中方法，首次获取进行数据库查询
     *
     * @return null|string
     */
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

        // 数据库查询
        $info = $this->orderMailRepo->getRepoRowByPrimaryKey($this->orderId);
        // 查询状态改变
        $this->hasQueryMailingTable = true;

        // 数据库不存在该订单邮寄信息，记录
        if (empty($info)) {
            return;
        }

        $this->consigneeName   = $info['consignee_name'];
        $this->consigneePhone  = $info['consignee_phone'];
        $this->shippingAddress = $info['shipping_address'];
        $this->shippingNo      = $info['shipping_no'];
        $this->shippingTime    = $info['shipping_time'];

        unset($info['order_id'], $info['created_at'], $info['updated_at'], $info['deleted_at']);
        $this->orderMailingArray = $info;
    }
}
