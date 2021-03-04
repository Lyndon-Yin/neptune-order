<?php
namespace App\Traits\Orders\OrderPayment;


use App\Repositories\Orders\OrderRepository;
use App\Repositories\Orders\OrderPaymentRepository;

/**
 * Trait BaseOrderPaymentTrait
 * @package App\Traits\Orders\OrderPayment
 *
 * @property OrderRepository $orderRepo
 * @property OrderPaymentRepository $orderPayRepo
 */
trait BaseOrderPaymentTrait
{
    // 支付类型，微信/支付等
    protected $paymentType = '';

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function pushPaymentType(string $paymentType)
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    // 微信/支付宝等支付金额
    protected $paymentTypeAmount = 0.00;

    public function getPaymentTypeAmount()
    {
        return $this->paymentTypeAmount;
    }

    public function pushPaymentTypeAmount(float $paymentTypeAmount)
    {
        $this->paymentTypeAmount = round($paymentTypeAmount, 2);
        return $this;
    }

    // 余额支付金额
    protected $accountBalanceAmount = 0.00;

    public function getAccountBalanceAmount()
    {
        return $this->accountBalanceAmount;
    }

    public function pushAccountBalanceAmount(float $accountBalanceAmount)
    {
        $this->accountBalanceAmount = round($accountBalanceAmount, 2);
        return $this;
    }

    // 支付完成时间
    protected $paymentTime = null;

    public function getPaymentTime()
    {
        return $this->paymentTime;
    }

    /**
     * 订单异常，释放账户余额支付金额锁定
     */
    protected function unlockAccountBalanceAmount()
    {

    }

    /**
     * 获取账户可使用额度，并锁定该余额
     *
     * @param float $accountBalanceAmount
     * @return float
     */
    protected function lockAccountBalanceAmount($accountBalanceAmount)
    {
        // 获取账户可使用额度，并锁定该余额

        return round($accountBalanceAmount, 2);
    }
}
