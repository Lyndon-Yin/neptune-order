<?php
namespace App\Services\Orders\NormalOrder;


use App\Traits\Orders\BaseOrderMailingTrait;
use App\Services\Orders\BaseCore\OrderCreateService;

/**
 * Class CreateNormalOrderService
 * @package App\Services\Orders\NormalOrder
 */
class CreateNormalOrderService extends OrderCreateService
{
    use BaseOrderMailingTrait;

    /**
     * @throws \Exception
     */
    protected function beforeTransaction()
    {
        parent::beforeTransaction();

        // 如果存在用户地址ID传参，初始化邮寄信息
        $this->initMailingInfoByUserAddressId();
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
