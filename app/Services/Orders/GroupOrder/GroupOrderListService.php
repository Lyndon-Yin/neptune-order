<?php
namespace App\Services\Orders\GroupOrder;


use App\Services\BaseService;
use App\Transformers\GroupOrder\GroupOrderTransformer;
use App\Traits\RepositoryLazyLoad\RepositoryLazyLoadOrder;

/**
 * Class GroupOrderListService
 * @package App\Services\Orders\GroupOrder
 */
class GroupOrderListService extends BaseService
{
    use RepositoryLazyLoadOrder;

    /**
     * 用户订单列表
     *
     * @param $param
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Lyndon\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function userOrderList($param)
    {
        $userId = hash_ids_decode($param['user_id']);
        if (empty($userId)) {
            throw new \Exception('未识别用户ID');
        }

        // 获取所有用户订单ID列表
        $this->orderRepo
            ->pushDefaultSearchFields(['user_id' => '='])
            ->pushDefaultSearch(['user_id' => $userId]);
        $allIds = $this->orderRepo->getAllIdsByCriteria();

        // 根据数据主键列表分页，查询出具体数据
        $pageSize = get_page_size($param);
        $result = $this->orderRepo->selfPaginate($allIds, $pageSize);

        $orderIds = $result['data'];

        // od_orders表查询
        $result['data'] = $this->orderRepo->getRepoListByPrimaryKeys($orderIds);

        // od_order_items表查询
        $orderItems = $this->orderItemRepo->getOrderItemsByOrderIds($orderIds);

        // od_order_mailing表查询
        $orderMailing = $this->orderMailRepo->getRepoListByPrimaryKeys($orderIds);
        $orderMailing = array_column($orderMailing, null, 'order_id');

        // od_order_mailing_home表查询
        $orderMailingHome = $this->orderMailHomeRepo->getRepoListByPrimaryKeys($orderIds);
        $orderMailingHome = array_column($orderMailingHome, null, 'order_id');

        // 数据合并
        foreach ($result['data'] as &$val) {
            $val['order_items'] = isset($orderItems[$val['id']]) ? $orderItems[$val['id']] : [];
            $val['order_mailing'] = isset($orderMailing[$val['id']]) ? $orderMailing[$val['id']] : [];
            $val['order_mailing_home'] = isset($orderMailingHome[$val['id']]) ? $orderMailingHome[$val['id']] : [];
        }
        unset($val, $orderItems);

        $result['data'] = (new GroupOrderTransformer($result['data'], 2))->toArray();

        return $result;
    }
}
