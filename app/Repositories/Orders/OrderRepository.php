<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderModel;

/**
 * Class OrderRepository
 * @package App\Repositories\Orders
 */
class OrderRepository extends BaseOrderRepository
{
    /**
     * @var array 可搜索字段
     */
    public $fieldSearchable = [
        'merchant_id'  => '=',
        'user_id'      => '=',
        'order_status' => '=',
    ];

    /**
     * @var array 默认排序
     */
    public $defaultOrderByFields = [
        'id' => 'desc'
    ];

    /**
     * @var array 排序字段别名形式
     */
    public $aliasOrderByFields = [
        'order_id' => 'id'
    ];

    public function model()
    {
        return OrderModel::class;
    }
}
