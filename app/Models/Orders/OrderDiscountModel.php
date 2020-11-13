<?php
namespace App\Models\Orders;

/**
 * Class OrderDiscountModel
 * @package App\Models\Orders
 */
class OrderDiscountModel extends BaseOrderModel
{
    protected $table = 'order_discount';

    public $tableColumn = [
        'order_id'        => 'int',
        'discount_type'   => 'int',
        'discount_amount' => 'float',
        'discount_remark' => 'string',
    ];
}
