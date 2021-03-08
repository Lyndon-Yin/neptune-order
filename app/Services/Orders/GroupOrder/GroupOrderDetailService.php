<?php
namespace App\Services\Orders\GroupOrder;


use App\Services\Orders\BaseCore\OrderDetailService;
use App\Traits\Orders\OrderMailing\DetailOrderMailingTrait;
use App\Traits\Orders\OrderPayment\DetailOrderPaymentTrait;

/**
 * Class GroupOrderDetailService
 * @package App\Services\Orders\GroupOrder
 */
class GroupOrderDetailService extends OrderDetailService
{
    use DetailOrderMailingTrait, DetailOrderPaymentTrait;

    /**
     * @var bool 是否查询过od_order_mailing_home表
     */
    protected $hasQueryMailingHomeTable = false;

    /**
     * @var array od_order_mailing_home表数据集
     */
    protected $orderMailingHomeArray = [];

    /**
     * @var bool 是否查询过od_order_group_buy表
     */
    protected $hasQueryGroupBuyTable = false;

    /**
     * @var array od_order_group_buy表数据集
     */
    protected $orderGroupBuyArray = [];

    /**
     * @var array 订单详情结果集
     */
    protected $orderResult = [];

    /**
     * GroupOrderDetailService constructor.
     * @param string $orderId
     * @throws \Exception
     */
    public function __construct(string $orderId)
    {
        parent::__construct($orderId);

        // 订单主表od_order_items表
        $this->orderResult = $this->getOrderArray();
    }

    /**
     * @return $this
     */
    public function pullOrderItems()
    {
        $this->orderResult['order_items'] = $this->getOrderItems();

        return $this;
    }

    /**
     * @return $this
     */
    public function pullOrderPayment()
    {
        $this->orderResult['order_payment'] = $this->getOrderPaymentArray();

        return $this;
    }

    /**
     * @return $this
     */
    public function pullOrderGroupBuy()
    {
        $this->orderResult['group_buy'] = $this->getOrderGroupBuyArray();

        return $this;
    }

    /**
     * @return $this
     */
    public function pullOrderMailing()
    {
        $this->orderResult['order_mailing'] = $this->getOrderMailingArray();

        return $this;
    }

    /**
     * @return $this
     */
    public function pullOrderMailingHome()
    {
        $this->orderResult['order_mailing_home'] = $this->getOrderMailingHomeArray();

        return $this;
    }

    /**
     * 返回结果集
     *
     * @return array
     */
    public function toResult()
    {
        return $this->orderResult;
    }

    /**
     * 获取od_order_group_buy表信息
     *
     * @return array
     */
    public function getOrderGroupBuyArray()
    {
        if ($this->hasQueryGroupBuyTable) {
            return $this->orderGroupBuyArray;
        }

        $this->orderGroupBuyArray = $this->orderGroupBuyRepo->getRepoRowByPrimaryKey($this->orderId);
        $this->hasQueryGroupBuyTable = true;

        return $this->orderGroupBuyArray;
    }

    /**
     * 获取od_order_mailing_home表信息
     *
     * @return array
     */
    public function getOrderMailingHomeArray()
    {
        if ($this->deliveryType != 3) {
            return [];
        }

        if ($this->hasQueryMailingHomeTable) {
            return $this->orderMailingHomeArray;
        }

        // od_order_mailing_home表查询
        $this->orderMailingHomeArray = $this->orderMailHomeRepo->getRepoRowByPrimaryKey($this->orderId);
        $this->hasQueryMailingHomeTable = true;

        return $this->orderMailingHomeArray;
    }
}
