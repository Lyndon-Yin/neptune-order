<?php
namespace App\innerApi;


use Lyndon\Logger\Log;

/**
 * Class GoodsAppApi
 * @package App\innerApi
 */
class GoodsAppApi extends BaseAppApi
{
    protected $arriveName = 'goods';

    /**
     * 根据商品实体ID获取实体信息
     *
     * @param int $merchantId
     * @param array $entityIds
     * @return array
     * @throws \Exception
     */
    public function getEntityByEntityIds(int $merchantId, array $entityIds)
    {
        Log::filename('GoodsAppApi')->info('getEntityByEntityIds', compact('merchantId', 'entityIds'));

        $results = $this->post(
            'shop-goods/goods/entity-by-entity-ids',
            ['merchant_id' => $merchantId, 'entity_ids' => $entityIds]
        );

        if (! $results['status']) {
            Log::filename('GoodsAppApi')->error('getEntityByEntityIds', compact('merchantId', 'entityIds', 'results'));
            throw new \Exception($results['message'], $results['code']);
        }

        Log::filename('GoodsAppApi')->info('getEntityByEntityIds', compact('results'));

        return $results['data'];
    }
}
