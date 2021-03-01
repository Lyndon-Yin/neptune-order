<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
use Lyndon\Exceptions\ValidatorException;
use App\Validators\Orders\GroupOrderValidator;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\OrderFacades\GroupOrderFacade;

/**
 * Class CancelOrder
 * @package App\Http\Controllers\GroupOrder\UserOrder
 */
class CancelOrder extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_POST;
    }

    /**
     * @api {post} /group-order/user-order/cancel-order
     *
     * @apiVersion 1.0.0
     * @apiGroup 用户团购订单
     *
     * @apiName UserOrder/CancelOrder
     * @apiDeprecated 用户取消团购订单
     *
     * @apiParam {String} order_id 订单ID
     * @apiParam {String} user_id 用户ID
     *
     * @apiParamExample {json} Request-Example:
     * {
     *    "order_id": "47555647020990464",
     *    "user_id": "WGqb2n"
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
        $param = $request->only('order_id', 'user_id');
        $validator = new GroupOrderValidator();
        try {
            $validator->with($param)
                ->pushMessage(['user_id.required' => '用户ID不能为空'])
                ->passesOrFail(GroupOrderValidator::RULE_ID, ['user_id' => 'required']);
        } catch (ValidatorException $e) {
            $errorData = $validator->getErrorData();
            return error_return(implode(',', $errorData), $e->getCode(), $errorData);
        }

        try {
            (new GroupOrderFacade())->groupOrderCancelByUser($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success');
    }
}
