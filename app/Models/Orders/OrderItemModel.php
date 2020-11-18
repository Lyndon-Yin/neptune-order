<?php
namespace App\Models\Orders;

/**
 * Class OrderItemModel
 * @package App\Models\Orders
 */
class OrderItemModel extends BaseOrderModel
{
    protected $table = 'order_items';

    public $tableColumn = [
        'order_id'             => 'int',
        'goods_id'             => 'int',
        'entity_id'            => 'int',
        'goods_name'           => 'string',
        'entity_img'           => 'string',
        'entity_price'         => 'float',
        'entity_spec_value'    => 'string',
        'buy_quantity'         => 'int',
        'item_total_amount'    => 'float',
        'item_discount_amount' => 'float',
        'item_payment_amount'  => 'float',
    ];
}
