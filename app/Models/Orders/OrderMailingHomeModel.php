<?php
namespace App\Models\Orders;

/**
 * Class OrderMailingHomeModel
 * @package App\Models\Orders
 */
class OrderMailingHomeModel extends BaseOrderModel
{
    protected $table = 'order_mailing_home';

    protected $primaryKey = 'order_id';

    public $tableColumn = [
        'order_id'         => 'int',
        'consignee_name'   => 'string',
        'consignee_phone'  => 'string',
        'shipping_address' => 'string',
        'point'            => 'null',
    ];
}
