<?php
namespace App\Models\Orders;

/**
 * Class OrderModel
 * @package App\Models\Orders
 */
class OrderModel extends BaseOrderModel
{
    protected $table = 'orders';

    protected $guarded = [];

    public $tableColumn = [
        'id'              => 'int',
        'merchant_id'     => 'int',
        'user_id'         => 'int',
        'order_category'  => 'int',
        'order_source'    => 'int',
        'total_amount'    => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'payment_amount'  => 'float',
        'delivery_type'   => 'int',
        'order_status'    => 'int',
        'order_time'      => 'null',
    ];
}
