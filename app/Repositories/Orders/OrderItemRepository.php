<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderItemModel;

/**
 * Class OrderItemRepository
 * @package App\Repositories\Orders
 */
class OrderItemRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderItemModel::class;
    }

    /**
     * 根据订单ID查询订单详情
     *
     * @param array $orderIds
     * @return mixed
     */
    public function getOrderItemsByOrderIds($orderIds)
    {
        $results = $this->model
            ->whereIn('order_id', $orderIds)
            ->orderBy('id', 'asc')
            ->get()->toArray();

        return array_group($results, 'order_id');
    }

    /**
     * 根据订单ID查询订单详情
     *
     * @param $orderId
     * @return mixed
     */
    public function getOrderItemsByOrderId($orderId)
    {
        $orderId = intval($orderId);

        return $this->model
            ->where('order_id', $orderId)
            ->orderBy('id', 'asc')
            ->get()->toArray();
    }
}
