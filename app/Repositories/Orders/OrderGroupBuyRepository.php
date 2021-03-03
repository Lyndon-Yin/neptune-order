<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderGroupBuyModel;

/**
 * Class OrderGroupBuyRepository
 * @package App\Repositories\Orders
 */
class OrderGroupBuyRepository extends BaseOrderRepository
{
    /**
     * @var array 可搜索字段
     */
    public $fieldSearchable = [
        'alpha_id' => '=',
        'order.order_status' => '=',
    ];

    /**
     * @var array 可搜索字段别名形式
     */
    public $aliasFieldSearchable = [
        'order_status' => 'order.order_status'
    ];

    /**
     * @var array 默认排序
     */
    public $defaultOrderByFields = [
        'order_id' => 'desc'
    ];

    public function model()
    {
        return OrderGroupBuyModel::class;
    }
}
