<?php
namespace App\Models\Orders;

/**
 * Class OrderMailingModel
 * @package App\Models\Orders
 */
class OrderMailingModel extends BaseOrderModel
{
    protected $table = 'order_mailing';

    protected $primaryKey = 'order_id';

    public $tableColumn = [
        'order_id'         => 'int',
        'consignee_name'   => 'string',
        'consignee_phone'  => 'string',
        'shipping_address' => 'string',
        'point'            => 'null',
        'shipping_no'      => 'string',
        'shipping_time'    => 'null',
    ];
}
