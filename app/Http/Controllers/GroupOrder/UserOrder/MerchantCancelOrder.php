<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
use Lyndon\Exceptions\ValidatorException;
use App\Validators\Orders\GroupOrderValidator;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\OrderFacades\GroupOrderFacade;

/**
 * Class MerchantCancelOrder
 * @package App\Http\Controllers\GroupOrder\UserOrder
 */
class MerchantCancelOrder extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_POST;
    }

    /**
     * @api {post} /group-order/user-order/merchant-cancel-order
     *
     * @apiVersion 1.0.0
     * @apiGroup 团购订单
     *
     * @apiName UserOrder/MerchantCancelOrder
     * @apiDeprecated 商家取消团购订单
     *
     * @apiParam {String} order_id 订单ID
     * @apiParam {String} merchant_id 商家ID
     *
     * @apiParamExample {json} Request-Example:
     * {
     *    "order_id": "47555647020990464",
     *    "merchant_id": "WGqb2n"
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
        $param = $request->only('order_id', 'merchant_id');
        $validator = new GroupOrderValidator();
        try {
            $validator->with($param)
                ->pushMessage(['merchant_id.required' => '商家ID不能为空'])
                ->passesOrFail(GroupOrderValidator::RULE_ID, ['merchant_id' => 'required']);
        } catch (ValidatorException $e) {
            $errorData = $validator->getErrorData();
            return error_return(implode(',', $errorData), $e->getCode(), $errorData);
        }

        try {
            (new GroupOrderFacade())->groupOrderCancelByMerchant($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success');
    }
}
