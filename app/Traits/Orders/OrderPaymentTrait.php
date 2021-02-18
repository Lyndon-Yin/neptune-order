<?php
namespace App\Traits\Orders;


use App\Models\Orders\OrderModel;
use Illuminate\Support\Facades\DB;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Orders\OrderPaymentRepository;

/**
 * Trait OrderPaymentTrait
 * @package App\Traits\Orders
 *
 * @property OrderRepository $orderRepo
 * @property OrderPaymentRepository $orderPayRepo
 */
trait OrderPaymentTrait
{
    // 支付类型，微信/支付等
    protected $paymentType = '';

    // 微信/支付宝等支付金额
    protected $paymentTypeAmount = 0.00;

    // 余额支付金额
    protected $accountBalanceAmount = 0.00;

    // 支付完成时间
    protected $paymentTime = null;

    // 支付单号，微信/支付宝等返回的参数
    protected $paymentNo = '';

    // 优先使用个人账户的钱付款
    protected $priorityPersonAccount = false;

    /**
     * 三方支付类型，微信/支付宝等
     *
     * @param string $paymentType
     * @return $this
     * @throws \Exception
     */
    public function pushPaymentType(string $paymentType)
    {
        if (! isset(OrderModel::PAYMENT_TYPE_ARRAY[$paymentType])) {
            throw new \Exception('未识别支付类型');
        }

        $this->paymentType = $paymentType;
        return $this;
    }

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
     * 账户余额支付金额
     *
     * @param float $accountBalanceAmount
     * @return $this
     */
    public function pushAccountBalanceAmount(float $accountBalanceAmount)
    {
        $this->accountBalanceAmount = round($accountBalanceAmount, 2);
        return $this;
    }

    /**
     * 三方支付金额
     *
     * @param float $paymentTypeAmount
     * @return $this
     */
    public function pushPaymentTypeAmount(float $paymentTypeAmount)
    {
        $this->paymentTypeAmount = round($paymentTypeAmount, 2);
        return $this;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function getPaymentTypeAmount()
    {
        return $this->paymentTypeAmount;
    }

    public function getAccountBalanceAmount()
    {
        return $this->accountBalanceAmount;
    }

    public function getPaymentTime()
    {
        return $this->paymentTime;
    }

    public function getPaymentNo()
    {
        return $this->paymentNo;
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
            $this->orderRepo->editRepoRow($this->orderId, ['order_status' => OrderModel::ORDER_WAIT_PAYED_COMPLETED]);

            // od_order_payment表创建或者更新
            if (! $this->orderPayRepo->existsRepoRowByPrimaryKey($this->orderId)) {
                $temp = [
                    'order_id' => $this->orderId,
                    'payment_type' => $this->paymentType,
                    'payment_type_amount' => $this->paymentTypeAmount,
                    'account_balance_amount' => $this->accountBalanceAmount
                ];
                $this->orderPayRepo->addRepoRow($temp);
            } else {
                $temp = [
                    'payment_type' => $this->paymentType,
                    'payment_type_amount' => $this->paymentTypeAmount,
                    'account_balance_amount' => $this->accountBalanceAmount
                ];
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
     * 支付完成
     *
     * @throws \Exception
     */
    protected function payComplete()
    {
        if (empty($this->orderId)) {
            throw new \Exception('订单ID不能为空');
        }
        // 验证订单状态
        if ($this->orderStatus != OrderModel::ORDER_WAIT_PAYED_COMPLETED) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }
        // 验证订单支付信息存在性
        if (! $this->orderPayRepo->existsRepoRowByPrimaryKey($this->orderId)) {
            throw new \Exception('订单异常，未识别支付信息');
        }

        try {
            DB::beginTransaction();

            // od_orders状态改变
            $this->orderRepo->editRepoRow($this->orderId, ['order_status' => OrderModel::ORDER_PAYED]);

            // od_order_payment表更新
            $this->orderPayRepo->editRepoRow($this->orderId, ['payment_time' => date('Y-m-d H:i:s')]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception($e->getMessage(), $e->getCode());
        }

        $this->orderStatus = OrderModel::ORDER_PAYED;
    }

    /**
     * 验证订单支付参数
     *
     * @throws \Exception
     */
    protected function validatePayAmount()
    {
        if (empty($this->orderId)) {
            throw new \Exception('订单ID不能为空');
        }
        if (empty($this->paymentAmount)) {
            throw new \Exception('支付金额不能小于等于0');
        }

        // 验证订单状态
        if (! in_array($this->orderStatus, [OrderModel::ORDER_INIT, OrderModel::ORDER_WAIT_PAYED_COMPLETED])) {
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
