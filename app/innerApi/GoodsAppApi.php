<?php
namespace App\innerApi;


use Lyndon\Traits\Singleton;

/**
 * Class GoodsAppApi
 * @package App\innerApi
 */
class GoodsAppApi extends BaseAppApi
{
    use Singleton;

    protected $arriveName = 'goods';

    /**
     * 获取团长开团信息
     *
     * @param int $alphaId      团长ID
     * @param int $alphaGroupId 团长开团ID
     * @param int $alphaBachId  团长开团批次ID
     * @return mixed
     * @throws \Exception
     */
    public function getAlphaGroupInfo($alphaId, $alphaGroupId, $alphaBachId)
    {
        $result = $this->get(
            'response-api/order-api/alpha-group-info2',
            [
                'alpha_id'       => $alphaId,
                'alpha_group_id' => $alphaGroupId,
                'alpha_batch_id' => $alphaBachId
            ]
        );

        if (! $result['status']) {
            throw new \Exception($result['message'], $result['code']);
        }

        return $result['data'];
    }

    /**
     * 根据商品实体ID获取实体信息
     *
     * @param int $merchantId   商家ID
     * @param int $groupId      团购模板ID
     * @param int $groupBatchId 团购模板批次ID
     * @param array $entities   购买的实体列表
     * @return array
     * @throws \Exception
     */
    public function getEntityByEntityIds($merchantId, $groupId, $groupBatchId, array $entities)
    {
        $results = $this->post(
            'response-api/order-api/group-entity-list',
            [
                'merchant_id'    => $merchantId,
                'group_id'       => $groupId,
                'group_batch_id' => $groupBatchId,
                'entities'       => $entities
            ]
        );

        if (! $results['status']) {
            throw new \Exception($results['message'], $results['code']);
        }

        return $results['data'];
    }

    /**
     * 订单扣库存
     *
     * @param int $merchantId   商家ID
     * @param int $groupId      团购模板ID
     * @param int $groupBatchId 团购模板批次ID
     * @param array $entities   购买的实体列表
     * @param string $orderId   订单ID
     * @return array
     * @throws \Exception
     */
    public function orderConsumeStock($merchantId, $groupId, $groupBatchId, array $entities, $orderId)
    {
        $results = $this->post(
            'response-api/order-api/order-consume-stock',
            [
                'merchant_id'    => $merchantId,
                'group_id'       => $groupId,
                'group_batch_id' => $groupBatchId,
                'entities'       => $entities,
                'order_id'       => $orderId
            ]
        );

        if (! $results['status']) {
            throw new \Exception($results['message'], $results['code']);
        }

        return $results['data'];
    }

    /**
     * 订单返还库存
     *
     * @param int $merchantId   商家ID
     * @param int $groupId      团购模板ID
     * @param int $groupBatchId 团购模板批次ID
     * @param array $entities   购买的实体列表
     * @param string $orderId   订单ID
     * @return array
     * @throws \Exception
     */
    public function orderReturnStock($merchantId, $groupId, $groupBatchId, array $entities, $orderId)
    {
        $results = $this->post(
            'response-api/order-api/order-return-stock',
            [
                'merchant_id'    => $merchantId,
                'group_id'       => $groupId,
                'group_batch_id' => $groupBatchId,
                'entities'       => $entities,
                'order_id'       => $orderId
            ]
        );

        if (! $results['status']) {
            throw new \Exception($results['message'], $results['code']);
        }

        return $results['data'];
    }
}
