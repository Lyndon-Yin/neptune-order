<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
use Lyndon\Exceptions\ValidatorException;
use App\Validators\Orders\GroupOrderValidator;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\OrderFacades\GroupOrderFacade;

/**
 * Class AddOrder
 * @package App\Http\Controllers\GroupOrder\UserOrder
 */
class AddOrder extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_POST;
    }

    /**
     * @api {post} /group-order/user-order/order-add
     *
     * @apiVersion 1.0.0
     * @apiGroup 团购订单
     *
     * @apiName UserOrder/AddOrder
     * @apiDeprecated 团购订单添加
     *
     * @apiParam {String} user_id 用户ID
     * @apiParam {String} alpha_id 团长ID
     * @apiParam {String} alpha_group_id 团长开团ID
     * @apiParam {String} alpha_batch_id 开团批次ID
     * @apiParam {Object[]} buy_entities 购买的商品集合
     * @apiParam {Integer} delivery_type 配送方式1：快递到家，2：自提，3：快递到自提点，团长配送到家
     * @apiParam {String} [user_address_id] 用户地址ID（自提配送方式下不传）
     *
     * @apiParam (buy_entities) {String} entity_id 实体ID
     * @apiParam (buy_entities) {Integer} quantity 购买的数量
     *
     * @apiParamExample {json} Request-Example:
     * {
     *    "user_id": "WGqb2n",
     *    "alpha_id": "WGqb2n",
     *    "alpha_group_id": "OGvNGR",
     *    "alpha_batch_id": "grJWGw",
     *    "delivery_type": "2",
     *    "user_address_id": "RXZgrV",
     *    "buy_entities": [{
     *       "entity_id": "grJWGw",
     *       "quantity": 10
     *    }, {
     *       "entity_id": "kX4O2A",
     *       "quantity": 1
     *    }]
     * }
     *
     * @apiSuccess {Boolean} status 状态码true
     * @apiSuccess {Number} code 具体状态码200等
     * @apiSuccess {String} message 状态信息提示
     * @apiSuccess {Array} data 返回结果集
     *
     * @apiSuccessExample Success-Response:
     * {
     *    "status": true,
     *    "code": 200,
     *    "message": "success",
     *    "data": []
     * }
     *
     * @apiUse ErrorReturn
     */
    public function onRun(Request $request)
    {
        // 表单验证
        $param = $request->only('user_id', 'alpha_id', 'alpha_group_id', 'alpha_batch_id', 'buy_entities', 'delivery_type',
            'user_address_id');
        $validator = new GroupOrderValidator();
        try {
            $validator->with($param)->passesOrFail(GroupOrderValidator::RULE_COMMON);
        } catch (ValidatorException $e) {
            $errorData = $validator->getErrorData();
            return error_return(implode(',', $errorData), $e->getCode(), $errorData);
        }

        try {
            (new GroupOrderFacade())->createGroupOrder($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success', 200);
    }
}
