<?php
namespace App\Services\Orders\BaseCore;

/**
 * Class OrderUpdateService
 * @package App\Services\Orders\BaseCore
 */
class OrderUpdateService extends OrderDetailService
{
    /**
     * 添加订单备注
     *
     * @param string $orderRemark
     * @return $this
     */
    public function pushOrderRemark($orderRemark)
    {
        $this->orderRemark = trim($orderRemark);

        return $this;
    }
}
