<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderMailingHomeModel;

/**
 * Class OrderMailingHomeRepository
 * @package App\Repositories\Orders
 */
class OrderMailingHomeRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderMailingHomeModel::class;
    }
}
