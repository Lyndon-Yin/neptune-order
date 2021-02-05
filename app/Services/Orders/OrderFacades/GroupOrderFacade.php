<?php
namespace App\Services\Orders\OrderFacades;


use App\Services\Orders\GroupOrder\CreateGroupOrderService;

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
}
