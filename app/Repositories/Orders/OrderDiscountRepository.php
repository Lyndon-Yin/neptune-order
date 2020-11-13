<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderDiscountModel;

/**
 * Class OrderDiscountRepository
 * @package App\Repositories\Orders
 */
class OrderDiscountRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderDiscountModel::class;
    }
}
