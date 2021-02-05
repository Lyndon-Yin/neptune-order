<?php
namespace App\Services\Orders\GroupOrder;


use App\innerApi\PaymentAppApi;
use App\Traits\Orders\OrderPaymentTrait;
use App\Services\Orders\BaseCore\OrderUpdateService;

/**
 * Class UpdateGroupOrderService
 * @package App\Services\Orders\GroupOrder
 */
class UpdateGroupOrderService extends OrderUpdateService
{
    use OrderPaymentTrait {doPay as protected traitDoPay;}

    /**
     * 订单支付
     *
     * @return mixed
     * @throws \Exception
     */
    public function doPay()
    {
        // 生成支付信息
        $this->traitDoPay();

        // 调起支付
        if ($this->paymentType == 'wx') {
            $result = (PaymentAppApi::getInstance())->wxPay(
                $this->userId,
                $this->merchantId,
                $this->orderId,
                $this->totalAmount,
                'group-order/wx-pay-notify'
            );
        } else {
            throw new \Exception('暂不支持该支付类型');
        }

        return $result;
    }
}
