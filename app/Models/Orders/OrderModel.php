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

    /** 支付类型 **/
    const PAYMENT_TYPE_ARRAY = [
        'wx'  => '微信支付',
        'ali' => '支付宝支付'
    ];

    /** 快递配送类型 **/
    // 无配送类型，默认值
    const DELIVERY_NONE = 0;
    // 邮寄到家
    const DELIVERY_MAILING = 1;
    // 自提点自提
    const DELIVERY_FETCH = 2;
    // 快递到自提点，配送到家
    const DELIVERY_FETCH_HOME = 3;

    /** 订单状态 **/
    // 未支付
    const ORDER_INIT = 0;
    // 等待支付完成状态
    const ORDER_WAIT_PAYED_COMPLETED = 9;
    // 已支付（商家）/待发货（会员）
    const ORDER_PAYED = 20;
    // 已发货（商家）/待收货（会员）
    const ORDER_SHIP = 40;
    // 已收货
    const ORDER_GET = 60;
    // 已完成
    const ORDER_COMPLETED = 80;

    // 未支付取消
    const ORDER_NO_PAY_CANCEL = 100;
    // 未支付取消已退款
    const ORDER_NO_PAY_CANCEL_COMPLETED = 81;
    // 已支付取消（商家）/待退款（会员）
    const ORDER_PAYED_CANCEL = 110;
    // 支付取消已退款
    const ORDER_PAYED_CANCEL_COMPLETED = 82;
}
