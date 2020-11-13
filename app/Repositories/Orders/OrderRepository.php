<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderModel;

/**
 * Class OrderRepository
 * @package App\Repositories\Orders
 */
class OrderRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderModel::class;
    }
}
