<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderMailingModel;

/**
 * Class OrderMailingRepository
 * @package App\Repositories\Orders
 */
class OrderMailingRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderMailingModel::class;
    }
}
