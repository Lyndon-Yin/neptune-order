<?php
namespace App\Validators\Orders;


use App\Validators\BaseValidator;

/**
 * Class GroupOrderValidator
 * @package App\Validators\ShopGoods
 */
class GroupOrderValidator extends BaseValidator
{
    const RULE_COMMON = 'rule_common';

    const RULE_ID = 'rule_id';

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [
        self::RULE_COMMON => [
            'user_id'        => 'required',
            'alpha_id'       => 'required',
            'alpha_group_id' => 'required',
            'alpha_batch_id' => 'required',
            'delivery_type'  => 'required|in:1,2,3',
            'buy_entities'   => 'required|array',
            'buy_entities.*.entity_id' => 'required',
            'buy_entities.*.quantity'  => 'required|integer|min:1',
        ],
        self::RULE_ID => [
            'order_id' => 'required',
        ]
    ];

    /**
     * 验证错误提示信息
     *
     * @var array
     */
    protected $message = [
        'order_id.required'       => '订单ID不能为空',
        'user_id.required'        => '用户ID不能为空',
        'alpha_id.required'       => '团长不能为空',
        'alpha_group_id.required' => '团长开团ID不能为空',
        'alpha_batch_id.required' => '开团批次ID不能为空',
        'delivery_type.required'  => '配送方式不能为空',
        'delivery_type.in'        => '未识别配送方式',
        'buy_entities.required'   => '购买的商品不能为空',
        'buy_entities.array'      => '购买的商品必须是数组类型',
        'buy_entities.*.entity_id.required' => '商品数组中实体ID不能为空',
        'buy_entities.*.quantity.required'  => '商品数组中购买的数量不能为空',
        'buy_entities.*.quantity.integer'   => '商品数组中购买的数量必须是整数',
        'buy_entities.*.quantity.min'       => '商品数组中购买的数量必须是大于0的整数',
    ];
}
