<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderPaymentModel;

/**
 * Class OrderPaymentRepository
 * @package App\Repositories\Orders
 */
class OrderPaymentRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderPaymentModel::class;
    }
}
