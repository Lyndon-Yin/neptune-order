<?php
namespace App\Traits\Orders\OrderMailing;

/**
 * Trait CreateOrderMailingTrait
 * @package App\Traits\Orders\OrderMailing
 */
trait CreateOrderMailingTrait
{
    use BaseOrderMailingTrait;

    // 用户地址ID
    private $userAddressId = 0;

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
        // 验证是否已经初始化过了订单邮寄信息
        static $hasInitMailingInfo = false;
        if ($hasInitMailingInfo) {
            return;
        }

        if ($this->userAddressId <= 0) {
            return;
        }
        if (! isset($this->userId) || $this->userId <= 0) {
            throw new \Exception('用户ID不能为空');
        }

        // 根据用户地址ID初始化邮寄信息

        // 订单邮寄信息已经初始化完成
        $hasInitMailingInfo = true;
    }

    /**
     * 添加od_order_mailing表
     *
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    protected function createOrderMailingTable()
    {
        if (empty($this->orderId)) {
            throw new \Exception('订单ID不能为空');
        }

        // 初始化订单邮寄信息
        $this->initMailingInfoByUserAddressId();

        $temp = [
            'order_id'         => $this->orderId,
            'consignee_name'   => $this->consigneeName,
            'consignee_phone'  => $this->consigneePhone,
            'shipping_address' => $this->shippingAddress
        ];

        $this->orderMailRepo->addRepoRow($temp);
    }
}
