<?php
namespace App\Models\Orders;

/**
 * Class OrderPaymentModel
 * @package App\Models\Orders
 */
class OrderPaymentModel extends BaseOrderModel
{
    protected $table = 'order_payment';

    protected $primaryKey = 'order_id';

    public $tableColumn = [
        'order_id'       => 'int',
        'payment_type'   => 'int',
        'payment_amount' => 'float',
        'payment_time'   => 'null',
        'payment_no'     => 'string',
        'account_balance_amount' => 'float',
    ];
}
