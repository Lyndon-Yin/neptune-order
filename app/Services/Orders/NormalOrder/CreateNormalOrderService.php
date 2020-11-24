<?php
namespace App\Services\Orders\NormalOrder;


use App\Services\Orders\BaseCore\OrderCreateService;
use App\Traits\Orders\OrderMailing\CreateOrderMailingTrait;

/**
 * Class CreateNormalOrderService
 * @package App\Services\Orders\NormalOrder
 */
class CreateNormalOrderService extends OrderCreateService
{
    use CreateOrderMailingTrait;

    /**
     * @throws \Exception
     */
    protected function beforeTransaction()
    {
        parent::beforeTransaction();

        // 如果存在用户地址ID传参，初始化邮寄信息
        $this->initMailingInfoByUserAddressId();

        // 强验证快递信息
        if (empty($this->consigneeName)) {
            throw new \Exception('收件人姓名不能为空');
        }
        if (empty($this->consigneePhone)) {
            throw new \Exception('收件人联系电话不能为空');
        }
        if (! is_mobile($this->consigneePhone)) {
            throw new \Exception('请输入正确的收件人联系电话');
        }
        if (empty($this->shippingAddress)) {
            throw new \Exception('收件人邮寄地址不能为空');
        }
    }

    /**
     * @return mixed|null|void
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    protected function doTransaction()
    {
        parent::doTransaction();

        // od_order_mailing表添加
        $this->createOrderMailingTable();
    }
}
