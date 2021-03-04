<?php
namespace App\Traits\Orders\OrderPayment;

/**
 * Trait DetailOrderPaymentTrait
 * @package App\Traits\Orders\OrderPayment
 */
trait DetailOrderPaymentTrait
{
    use BaseOrderPaymentTrait;

    /**
     * @var bool 是否已经查询过订单支付表
     */
    protected $hasQueryPaymentTable = false;

    /**
     * 获取订单支付信息数组
     *
     * @return array
     */
    public function getOrderPaymentArray()
    {
        $this->initPaymentInfo();

        return [
            'payment_type'           => $this->paymentType,
            'payment_type_amount'    => $this->paymentTypeAmount,
            'account_balance_amount' => $this->accountBalanceAmount,
            'payment_time'           => $this->paymentTime
        ];
    }

    public function getPaymentType()
    {
        $this->initPaymentInfo();

        return $this->paymentType;
    }

    public function getPaymentTypeAmount()
    {
        $this->initPaymentInfo();

        return $this->paymentTypeAmount;
    }

    public function getAccountBalanceAmount()
    {
        $this->initPaymentInfo();

        return $this->accountBalanceAmount;
    }

    public function getPaymentTime()
    {
        $this->initPaymentInfo();

        return $this->paymentTime;
    }

    /**
     * 初始化订单支付信息
     */
    protected function initPaymentInfo()
    {
        if (empty($this->orderId)) {
            return;
        }
        if ($this->hasQueryPaymentTable) {
            return;
        }

        // 查询od_order_payment表
        $tmp = $this->orderPayRepo->getRepoRowByPrimaryKey($this->orderId);
        $this->hasQueryPaymentTable = true;
        if (empty($tmp)) {
            return;
        }

        $this->paymentType          = $tmp['payment_type'];
        $this->paymentTypeAmount    = $tmp['payment_type_amount'];
        $this->accountBalanceAmount = $tmp['account_balance_amount'];
        $this->paymentTime          = $tmp['payment_time'];
    }
}
