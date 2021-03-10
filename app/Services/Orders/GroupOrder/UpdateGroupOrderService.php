<?php
namespace App\Services\Orders\GroupOrder;


use App\innerApi\PaymentAppApi;
use App\Models\Orders\OrderModel;
use Lyndon\RedisLock\OptimisticLockTrait;
use App\Services\Orders\BaseCore\OrderUpdateService;
use App\Traits\Orders\OrderPayment\CreateOrderPaymentTrait;
use App\Traits\Orders\OrderPayment\UpdateOrderPaymentTrait;
use App\Traits\Orders\OrderMailing\UpdateOrderMailingTrait;

/**
 * Class UpdateGroupOrderService
 * @package App\Services\Orders\GroupOrder
 */
class UpdateGroupOrderService extends OrderUpdateService
{
    use OptimisticLockTrait, UpdateOrderMailingTrait;
    use CreateOrderPaymentTrait, UpdateOrderPaymentTrait {
        doPay as protected traitDoPay;
        payComplete as protected traitPayComplete;

        // 解决方法冲突，使用UpdateOrderPaymentTrait中的方法
        UpdateOrderPaymentTrait::getPaymentTime insteadof CreateOrderPaymentTrait;
        UpdateOrderPaymentTrait::getPaymentType insteadof CreateOrderPaymentTrait;
        UpdateOrderPaymentTrait::getPaymentTypeAmount insteadof CreateOrderPaymentTrait;
        UpdateOrderPaymentTrait::getAccountBalanceAmount insteadof CreateOrderPaymentTrait;
    }

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

        // 不用进行三方支付，直接支付完成
        if ($this->paymentTypeAmount < 0.01) {
            $this->payComplete();

            return ['pay_complete' => 1];
        }

        try {
            // 获取订单团购信息
            $body = $this->orderGroupBuyRepo->getRepoRowByPrimaryKey($this->orderId);
            $body = "团购：" . $body['alpha_group_name'];

            // 调起三方支付
            if ($this->paymentType == 'wx') {
                $result = (PaymentAppApi::getInstance())->wxPay(
                    $this->userId,
                    $this->merchantId,
                    $this->orderId,
                    $this->paymentTypeAmount,
                    'pay-notify/group-order/wx-pay-notify',
                    $body
                );
            } else {
                throw new \Exception('暂不支持该支付类型');
            }
        } catch (\Exception $e) {
            // 调起支付异常，将订单状态回退
            $this->rollbackDoPay();

            throw $e;
        }

        return $result;
    }

    /**
     * 支付完成
     *
     * @throws \Exception
     */
    public function payComplete()
    {
        // redis乐观锁实现幂等性验证
        $redisKey = ':idempotent:order:pay-complete:' . $this->orderId;
        if (! $this->addOptimisticLock($redisKey, 1, 60)) {
            // 添加失败，说明该订单已经成功支付
            return;
        }

        try {
            // od_orders表更新
            // od_order_payment表更新
            $this->traitPayComplete();
        } catch (\Exception $e) {
            // 释放乐观锁
            $this->delOptimisticLock($redisKey);

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 支付失败
     *
     * @throws \Exception
     */
    public function payFail()
    {
        // 只有等待支付完成订单才可以支付失败
        if ($this->orderStatus != OrderModel::ORDER_WAIT_PAID_COMPLETED) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }

        // redis乐观锁实现幂等性验证
        $redisKey = ':idempotent:order:pay-fail:' . $this->orderId;
        if (! $this->addOptimisticLock($redisKey, 1, 60)) {
            // 添加失败，说明该订单状态已经更改
            return;
        }

        try {
            // od_orders表更新
            $this->orderRepo->editRepoRow(
                $this->orderId,
                [
                    'order_status' => OrderModel::ORDER_PAID_FAILED,
                    'order_remark' => date('Y-m-d H:i:s') . ' 支付失败'
                ]
            );
        } catch (\Exception $e) {
            // 释放乐观锁
            $this->delOptimisticLock($redisKey);

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 未支付订单取消
     *
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    public function cancelOrder()
    {
        // 只有未支付订单才可以取消
        if ($this->orderStatus != OrderModel::ORDER_INIT) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }

        // od_orders表更新
        $this->orderRepo->editRepoRow(
            $this->orderId,
            [
                'order_status' => OrderModel::ORDER_UNPAID_CANCEL,
                'order_remark' => $this->orderRemark
            ]
        );

        $this->orderStatus = OrderModel::ORDER_UNPAID_CANCEL;
    }

    /**
     * 订单核销（订单完成）
     *
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    public function orderVerification()
    {
        // 只有已支付订单可以核销
        if ($this->orderStatus != OrderModel::ORDER_PAID) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }

        $this->orderRepo->editRepoRow(
            $this->orderId,
            ['order_status' => OrderModel::ORDER_COMPLETED]
        );

        $this->orderStatus = OrderModel::ORDER_COMPLETED;
    }
}
