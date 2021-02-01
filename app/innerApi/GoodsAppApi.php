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
     * @param int $alphaId      团长ID
     * @param int $alphaGroupId 团长开团ID
     * @param int $alphaBachId  团长开团批次ID
     * @param array $entities   购买的实体列表
     * @return array
     * @throws \Exception
     */
    public function getEntityByEntityIds($alphaId, $alphaGroupId, $alphaBachId, array $entities)
    {
        $results = $this->post(
            'response-api/order-api/alpha-group-entity-list',
            [
                'alpha_id'       => $alphaId,
                'alpha_group_id' => $alphaGroupId,
                'alpha_batch_id' => $alphaBachId,
                'entities'       => $entities
            ]
        );

        if (! $results['status']) {
            throw new \Exception($results['message'], $results['code']);
        }

        return $results['data'];
    }
}
