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
