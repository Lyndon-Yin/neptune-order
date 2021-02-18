<?php
namespace App\Services\Orders\GroupOrder;


use App\innerApi\PaymentAppApi;
use App\Traits\Orders\OrderPaymentTrait;
use Lyndon\RedisLock\OptimisticLockTrait;
use App\Services\Orders\BaseCore\OrderUpdateService;

/**
 * Class UpdateGroupOrderService
 * @package App\Services\Orders\GroupOrder
 */
class UpdateGroupOrderService extends OrderUpdateService
{
    use OptimisticLockTrait;
    use OrderPaymentTrait {
        doPay as protected traitDoPay;
        payComplete as protected traitPayComplete;
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

        // 调起支付
        if ($this->paymentType == 'wx') {
            $result = (PaymentAppApi::getInstance())->wxPay(
                $this->userId,
                $this->merchantId,
                $this->orderId,
                $this->totalAmount,
                'pay-notify/group-order/wx-pay-notify'
            );
        } else {
            throw new \Exception('暂不支持该支付类型');
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
}
