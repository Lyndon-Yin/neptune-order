<?php
namespace App\Traits\Orders;


use App\Repositories\Orders\OrderMailingRepository;

/**
 * Trait BaseOrderMailingTrait
 * @package App\Traits\Orders
 *
 * @property OrderMailingRepository $orderMailRepo
 *
 * @property string $consigneeName 收货人姓名
 * @property string $consigneePhone 收货人联系方式
 * @property string $shippingAddress 收货人地址
 * @property string $shippingNo 发货快递单号
 * @property string $shippingTime 发货时间
 */
trait BaseOrderMailingTrait
{
    // 用户地址ID
    private $userAddressId = 0;

    /** 订单邮寄信息表od_order_mailing **/
    // 收货人姓名
    protected $consigneeName = '';

    // 收货人联系方式
    protected $consigneePhone = '';

    // 收货人地址
    protected $shippingAddress = '';

    // 发货快递单号
    protected $shippingNo = '';

    // 发货时间
    protected $shippingTime = null;

    /**
     * 添加邮寄信息
     *
     * @param array $param
     * @return $this
     */
    public function pushMailingInfo(array $param)
    {
        $this->consigneeName   = empty($param['consignee_name']) ? '' : trim($param['consignee_name']);
        $this->consigneePhone  = empty($param['consignee_phone']) ? '' : trim($param['consignee_phone']);
        $this->shippingAddress = empty($param['shipping_address']) ? '' : trim($param['shipping_address']);

        return $this;
    }

    /**
     * 添加用户地址，自动查询地址信息
     *
     * @param int $userAddressId
     * @return $this
     */
    public function pushUserAddressId(int $userAddressId)
    {
        $this->userAddressId = intval($userAddressId);

        return $this;
    }

    /**
     * 根据用户地址ID初始化邮寄信息
     * 存在覆盖pushMailingInfo传参的可能
     *
     * @throws \Exception
     */
    protected function initMailingInfoByUserAddressId()
    {
        if ($this->userAddressId <= 0) {
            return;
        }
        if ($this->userId <= 0) {
            throw new \Exception('用户ID不能为空');
        }

        // 根据用户地址ID初始化邮寄信息
    }

    /**
     * 添加od_order_mailing表
     *
     * @throws \Lyndon\Exceptions\ModelException
     */
    protected function createOrderMailingTable()
    {
        $temp = [
            'order_id'         => $this->orderId,
            'consignee_name'   => $this->consigneeName,
            'consignee_phone'  => $this->consigneePhone,
            'shipping_address' => $this->shippingAddress
        ];

        $this->orderMailRepo->addRepoRow($temp);
    }
}
