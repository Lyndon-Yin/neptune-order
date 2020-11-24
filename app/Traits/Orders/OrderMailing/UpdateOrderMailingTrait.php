<?php
namespace App\Traits\Orders\OrderMailing;


use App\Models\Orders\OrderModel;
use Illuminate\Support\Facades\DB;

/**
 * Trait UpdateOrderMailingTrait
 * @package App\Traits\Orders\OrderMailing
 */
trait UpdateOrderMailingTrait
{
    use BaseOrderMailingTrait;

    // 是否已经查询过邮寄信息表
    private $hasQueryMailingTable = false;

    // 是否存在该订单邮寄信息行
    private $hasMailingLine = true;

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
     * 订单发货
     *
     * @param string $shippingNo
     * @param string $shippingTime
     * @throws \Exception
     */
    public function doShipping($shippingNo, $shippingTime = '')
    {
        // 验证订单状态，只有已支付可以进行发货
        if ($this->orderStatus != OrderModel::ORDER_PAYED) {
            throw new \Exception('订单状态异常' . $this->orderStatus);
        }

        // 订单邮寄信息存在性验证
        $this->initMailingInfo();
        if (! $this->hasMailingLine) {
            throw new \Exception('订单异常，未找到该订单邮寄信息记录');
        }

        // 订单配送类型验证

        if (empty($shippingTime)) {
            $shippingTime = date('Y-m-d H:i:s');
        } else {
            // 时间传参格式标准化，时间格式未能正常解析，则用当前时间代替
            $shippingTime = strtotime($shippingTime);
            $shippingTime = ($shippingTime === false) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $shippingTime);
        }

        // 订单更改为配送状态
        $shippingNo = trim($shippingNo);
        DB::transaction(function () use ($shippingNo, $shippingTime) {
            // od_order_mailing表更改
            $this->orderMailRepo->editRepoRow(
                $this->orderId,
                [
                    'shipping_no'   => $shippingNo,
                    'shipping_time' => $shippingTime
                ]
            );

            // od_orders表更改
            $this->orderRepo->editRepoRow(
                $this->orderId,
                ['order_status' => OrderModel::ORDER_SHIP]
            );
        });

        // 当前对象的订单状态扭转
        $this->shippingNo   = $shippingNo;
        $this->shippingTime = $shippingTime;
        $this->orderStatus  = OrderModel::ORDER_SHIP;
    }

    /**
     * 初始化订单邮寄信息
     */
    private function initMailingInfo()
    {
        if ($this->hasQueryMailingTable) {
            return;
        }

        // 数据库查询
        $info = $this->orderMailRepo->getRepoRowByPrimaryKey($this->orderId);
        // 查询状态改变
        $this->hasQueryMailingTable = true;

        // 数据库不存在该订单邮寄信息，记录
        if (empty($info)) {
            $this->hasMailingLine = false;
            return;
        }

        $this->consigneeName   = $info['consignee_name'];
        $this->consigneePhone  = $info['consignee_phone'];
        $this->shippingAddress = $info['shipping_address'];
        $this->shippingNo      = $info['shipping_no'];
        $this->shippingTime    = $info['shipping_time'];
    }
}