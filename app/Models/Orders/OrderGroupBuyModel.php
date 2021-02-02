<?php
namespace App\Models\Orders;

/**
 * Class OrderGroupBuyModel
 * @package App\Models\Orders
 */
class OrderGroupBuyModel extends BaseOrderModel
{
    protected $table = 'order_group_buy';

    protected $primaryKey = 'order_id';

    public $tableColumn = [
        'order_id'         => 'int',
        'group_id'         => 'int',
        'group_batch_id'   => 'int',
        'alpha_id'         => 'int',
        'alpha_group_id'   => 'int',
        'alpha_batch_id'   => 'int',
        'alpha_group_name' => 'string',
        'arrival_date'     => 'null',
        'arrival_time'     => 'string',
    ];
}
