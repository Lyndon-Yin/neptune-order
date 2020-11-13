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
}
