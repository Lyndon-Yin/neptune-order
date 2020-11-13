<?php
namespace App\Models\Orders;

/**
 * Class OrderModel
 * @package App\Models\Orders
 */
class OrderModel extends BaseOrderModel
{
    protected $table = 'order';

    public $tableColumn = [
        'merchant_id'     => 'int',
        'user_id'         => 'int',
        'order_category'  => 'int',
        'total_amount'    => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'payment_amount'  => 'float',
        'delivery_type'   => 'int',
        'order_status'    => 'int',
        'order_time'      => 'null',
    ];
}
