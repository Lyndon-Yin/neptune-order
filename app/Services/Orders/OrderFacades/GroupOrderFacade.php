<?php
namespace App\Services\Orders\OrderFacades;


use App\Transformers\GroupOrder\GroupOrderTransformer;
use App\Services\Orders\GroupOrder\CreateGroupOrderService;
use App\Services\Orders\GroupOrder\GroupOrderDetailService;
use App\Services\Orders\GroupOrder\UpdateGroupOrderService;

/**
 * Class GroupOrderFacade
 * @package App\Services\Orders\OrderFacades
 */
class GroupOrderFacade
{
    /**
     * 订单创建
     *
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public function createGroupOrder(array $param)
    {
        // 参数解密
        $param['alpha_id'] = hash_ids_decode($param['alpha_id']);
        if (empty($param['alpha_id'])) {
            throw new \Exception('未识别团长ID');
        }
        $param['alpha_group_id'] = hash_ids_decode($param['alpha_group_id']);
        if (empty($param['alpha_group_id'])) {
            throw new \Exception('未识别团长开团ID');
        }
        $param['alpha_batch_id'] = hash_ids_decode($param['alpha_batch_id']);
        if (empty($param['alpha_batch_id'])) {
            throw new \Exception('未识别团长开团批次ID');
        }
        $param['user_id'] = hash_ids_decode($param['user_id']);
        if (empty($param['user_id'])) {
            throw new \Exception('未识别用户ID');
        }

        $param['buy_entities'] = array_map(function ($val) {
            $val['entity_id'] = hash_ids_decode($val['entity_id']);
            return $val;
        }, $param['buy_entities']);

        // 初始化订单创建类
        $orderObj = new CreateGroupOrderService(
            $param['alpha_id'],
            $param['alpha_group_id'],
            $param['alpha_batch_id'],
            $param['user_id'],
            $param['buy_entities']
        );

        // 推入配送方式和邮寄地址
        if (empty($param['user_address_id'])) {
            $userAddressId = 0;
        } else {
            $userAddressId = hash_ids_decode($param['user_address_id']);
            if (empty($userAddressId)) {
                throw new \Exception('未识别用户收获地址ID');
            }
        }
        $orderObj->pushDeliveryType($param['delivery_type'])->pushUserAddressId($userAddressId);

        // 订单创建
        $orderObj->createOrder();

        return ['order_id' => $orderObj->getOrderId()];
    }

    /**
     * 订单支付
     *
     * @param $param
     * @return mixed
     * @throws \Exception
     */
    public function payGroupOrder($param)
    {
        $orderObj = new UpdateGroupOrderService($param['order_id']);

        // 创建支付信息
        return $orderObj->pushPaymentType('wx')->doPay();
    }

    /**
     * 用户取消未支付订单
     *
     * @param $param
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    public function groupOrderCancelByUser($param)
    {
        $userId = hash_ids_decode($param['user_id']);
        if (empty($userId)) {
            throw new \Exception('未识别用户ID');
        }

        $orderObj = new UpdateGroupOrderService($param['order_id']);
        if ($orderObj->getUserId() != $userId) {
            throw new \Exception('未识别该订单ID');
        }

        $orderObj->pushOrderRemark('用户取消')->cancelOrder();
    }

    /**
     * 商家取消未支付订单
     *
     * @param $param
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    public function groupOrderCancelByMerchant($param)
    {
        $merchantId = hash_ids_decode($param['merchant_id']);
        if (empty($merchantId)) {
            throw new \Exception('未识别商家ID');
        }

        $orderObj = new UpdateGroupOrderService($param['order_id']);
        if ($orderObj->getMerchantId() != $merchantId) {
            throw new \Exception('未识别该订单ID');
        }

        $orderObj->pushOrderRemark('商家取消')->cancelOrder();
    }

    /**
     * 商家订单发货
     *
     * @param array $param
     * @throws \Exception
     */
    public function groupOrderShipping($param)
    {
        $merchantId = hash_ids_decode($param['merchant_id']);
        if (empty($merchantId)) {
            throw new \Exception('未识别商家ID');
        }

        $orderObj = new UpdateGroupOrderService($param['order_id']);
        if ($orderObj->getMerchantId() != $merchantId) {
            throw new \Exception('未识别该订单ID');
        }

        // 订单发货操作
        $param['shipping_no']   = trim($param['shipping_no']);
        $param['shipping_time'] = empty($param['shipping_time']) ? '' : trim($param['shipping_time']);
        $orderObj->doShipping($param['shipping_no'], $param['shipping_time']);
    }

    /**
     * 订单核销（确认收货）
     *
     * @param $param
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    public function groupOrderVerification($param)
    {
        $userId = hash_ids_decode($param['user_id']);
        if (empty($userId)) {
            throw new \Exception('未识别用户ID');
        }

        $orderObj = new UpdateGroupOrderService($param['order_id']);
        if ($orderObj->getUserId() != $userId) {
            throw new \Exception('未识别该订单ID');
        }

        $orderObj->orderVerification();
    }

    /**
     * 订单详情
     *
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public function groupOrderDetail($param)
    {
        $obj = new GroupOrderDetailService($param['order_id']);

        // 根据不同角色，进行数据安全验证
        if (isset($param['merchant_id'])) {
            $merchantId = hash_ids_decode($param['merchant_id']);
            if (empty($merchantId)) {
                throw new \Exception('未识别商家ID');
            }

            if ($obj->getMerchantId() != $merchantId) {
                return [];
            }
        }
        if (isset($param['user_id'])) {
            $userId = hash_ids_decode($param['user_id']);
            if (empty($userId)) {
                throw new \Exception('未识别用户ID');
            }

            if ($obj->getUserId() != $userId) {
                return [];
            }
        }

        // 数据库查询
        $result = $obj->pullOrderItems()
            ->pullOrderGroupBuy()
            ->pullOrderPayment()
            ->pullOrderMailing()
            ->pullOrderMailingHome()
            ->toResult();

        return (new GroupOrderTransformer($result, 1))->toArray();
    }
}
