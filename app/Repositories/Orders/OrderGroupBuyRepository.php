<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderGroupBuyModel;

/**
 * Class OrderGroupBuyRepository
 * @package App\Repositories\Orders
 */
class OrderGroupBuyRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderGroupBuyModel::class;
    }
}
