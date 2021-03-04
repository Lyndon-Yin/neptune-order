<?php
namespace App\Traits\Orders\OrderPayment;


use App\Models\Orders\OrderModel;
use Illuminate\Support\Facades\DB;

/**
 * Trait CreateOrderPaymentTrait
 * @package App\Traits\Orders\OrderPayment
 */
trait CreateOrderPaymentTrait
{
    use BaseOrderPaymentTrait;

    // 优先使用个人账户的钱付款
    protected $priorityPersonAccount = false;

    /**
     * 是否优先使用个人账户金额
     *
     * @param bool $priority
     * @return $this
     */
    public function priorityPersonAccount(bool $priority = true)
    {
        $this->priorityPersonAccount = $priority;
        return $this;
    }

    /**
     * 订单支付
     *
     * @throws \Exception
     */
    protected function doPay()
    {
        // 支付参数验证
        $this->validatePayAmount();

        // 验证是否已经存在支付信息
        try {
            DB::beginTransaction();

            // od_orders状态改变
            $this->orderRepo->editRepoRow(
                $this->orderId,
                ['order_status' => OrderModel::ORDER_WAIT_PAYED_COMPLETED]
            );

            // od_order_payment表创建或者更新
            $temp = [
                'payment_type'           => $this->paymentType,
                'payment_type_amount'    => $this->paymentTypeAmount,
                'account_balance_amount' => $this->accountBalanceAmount
            ];
            if (! $this->orderPayRepo->existsRepoRowByPrimaryKey($this->orderId)) {
                $temp['order_id'] = $this->orderId;
                $this->orderPayRepo->addRepoRow($temp);
            } else {
                $this->orderPayRepo->editRepoRow($this->orderId, $temp);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            // 订单异常，释放这部分锁定的余额额度
            $this->unlockAccountBalanceAmount();

            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->orderStatus = OrderModel::ORDER_WAIT_PAYED_COMPLETED;
    }

    /**
     * 验证订单支付参数
     *
     * @throws \Exception
     */
    protected function validatePayAmount()
    {
        if (empty($this->paymentAmount)) {
            throw new \Exception('支付金额不能小于等于0');
        }
        if (! isset(OrderModel::PAYMENT_TYPE_ARRAY[$this->paymentType])) {
            throw new \Exception('未识别支付类型' . $this->paymentType);
        }

        // 验证订单状态
        $noPayStatus = [OrderModel::ORDER_INIT, OrderModel::ORDER_WAIT_PAYED_COMPLETED];
        if (! in_array($this->orderStatus, $noPayStatus)) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }

        if ($this->priorityPersonAccount) {
            // 优先使用账户余额
            // 将支付金额paymentAmount传入，验证可使用额度并锁定该额度
            $this->accountBalanceAmount = $this->lockAccountBalanceAmount($this->paymentAmount);
            $this->paymentTypeAmount = round($this->paymentAmount - $this->accountBalanceAmount, 2);
            if ($this->paymentTypeAmount < 0) {
                // 订单异常，释放这部分锁定的余额额度
                $this->unlockAccountBalanceAmount();
                throw new \Exception('订单异常，请联系管理员');
            }
        } else {
            // 不优先使用账户余额
            // 通过传参来确定余额/三方支付金额。未传参，默认走三方支付
            // 金额的最小单位到分，所以0.009这样的值，就认为是0
            if ($this->accountBalanceAmount >= 0.01 && $this->paymentTypeAmount >= 0.01) {
                // 验证支付总金额是否等于支付金额
                $temp = round($this->accountBalanceAmount + $this->paymentTypeAmount, 2);
                if (abs($this->paymentAmount - $temp) >= 0.01) {
                    throw new \Exception('账户余额和三方支付金额不等于订单金额');
                }
            } elseif ($this->paymentTypeAmount >= 0.01) {
                $this->accountBalanceAmount = round($this->paymentAmount - $this->paymentTypeAmount, 2);
                if ($this->accountBalanceAmount < 0) {
                    throw new \Exception('三方支付金额大于订单金额');
                }
            } elseif ($this->accountBalanceAmount >= 0.01) {
                $this->paymentTypeAmount = round($this->paymentAmount - $this->accountBalanceAmount, 2);
                if ($this->paymentTypeAmount < 0) {
                    throw new \Exception('账户余额支付金额大于订单金额');
                }
            } else {
                // 默认直接走三方支付
                $this->paymentTypeAmount = round($this->paymentAmount, 2);
                $this->accountBalanceAmount = 0.00;
            }

            // 验证账户余额是否足够
            if ($this->accountBalanceAmount >= 0.01) {
                $temp = $this->lockAccountBalanceAmount($this->accountBalanceAmount);
                if (abs($temp - $this->accountBalanceAmount) >= 0.01) {
                    // 订单异常，释放这部分锁定的余额额度
                    $this->unlockAccountBalanceAmount();
                    throw new \Exception('账户余额不足');
                }
            }
        }

        // 三方支付大于0，必填三方支付类型
        if ($this->paymentTypeAmount >= 0.01) {
            if (empty($this->paymentType) || ! isset(OrderModel::PAYMENT_TYPE_ARRAY[$this->paymentType])) {
                // 订单异常，释放这部分锁定的余额额度
                $this->unlockAccountBalanceAmount();
                throw new \Exception('未选择付款方式');
            }
        }
    }
}
