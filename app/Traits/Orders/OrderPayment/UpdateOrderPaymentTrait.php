<?php
namespace App\Traits\Orders\OrderPayment;


use App\Models\Orders\OrderModel;
use Illuminate\Support\Facades\DB;

/**
 * Trait UpdateOrderPaymentTrait
 * @package App\Traits\Orders\OrderPayment
 */
trait UpdateOrderPaymentTrait
{
    use DetailOrderPaymentTrait;

    /**
     * 支付中断，回退支付状态
     *
     * @throws \Lyndon\Exceptions\ModelException
     */
    protected function rollbackDoPay()
    {
        // od_orders状态改变
        $this->orderRepo->editRepoRow($this->orderId, ['order_status' => OrderModel::ORDER_INIT]);

        // 释放锁定的余额额度
        $this->unlockAccountBalanceAmount();

        $this->orderStatus = OrderModel::ORDER_INIT;
    }

    /**
     * 支付完成
     *
     * @throws \Exception
     */
    protected function payComplete()
    {
        // 验证订单状态
        if ($this->orderStatus != OrderModel::ORDER_WAIT_PAYED_COMPLETED) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }

        // 验证订单支付信息存在性
        $this->initPaymentInfo();
        if ($this->paymentTypeAmount < 0.01 && $this->accountBalanceAmount < 0.01) {
            throw new \Exception('订单异常，未识别支付信息');
        }

        $currentDatetime = date('Y-m-d H:i:s');
        try {
            DB::beginTransaction();

            // od_orders状态改变
            $this->orderRepo->editRepoRow($this->orderId, ['order_status' => OrderModel::ORDER_PAYED]);

            // od_order_payment表更新
            $this->orderPayRepo->editRepoRow($this->orderId, ['payment_time' => $currentDatetime]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->paymentTime = $currentDatetime;
        $this->orderStatus = OrderModel::ORDER_PAYED;
    }
}
