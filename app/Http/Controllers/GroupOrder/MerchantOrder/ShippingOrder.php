<?php
namespace App\Http\Controllers\GroupOrder\MerchantOrder;


use Illuminate\Http\Request;
use Lyndon\Constant\CodeType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\OrderFacades\GroupOrderFacade;

/**
 * Class ShippingOrder
 * @package App\Http\Controllers\GroupOrder\MerchantOrder
 */
class ShippingOrder extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_POST;
    }

    /**
     * @api {post} /group-order/merchant-order/shipping-order
     *
     * @apiVersion 1.0.0
     * @apiGroup 商家团购订单
     *
     * @apiName MerchantOrder/ShippingOrder
     * @apiDeprecated 商家订单发货
     *
     * @apiParam {String} order_id 订单ID
     * @apiParam {String} merchant_id 商家ID
     * @apiParam {String} shipping_no 发货单号
     * @apiParam {String} [shipping_time] 发货时间，不传则发货时间为当前时间
     *
     * @apiParamExample {json} Request-Example:
     * {
     *    "order_id": "47555647020990464",
     *    "merchant_id": "WGqb2n",
     *    "shipping_no": "SF1308937518184"
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
        $param = $request->only('merchant_id', 'order_id', 'shipping_no', 'shipping_time');
        $validate = Validator::make(
            $param,
            [
                'order_id'    => 'required',
                'merchant_id' => 'required',
                'shipping_no' => 'required'
            ], [
                'order_id.required'    => '订单ID不能为空',
                'merchant_id.required' => '商家ID不能为空',
                'shipping_no.required' => '发货单号不能为空'
            ]
        );
        if ($validate->fails()) {
            $errorData = $validate->errors()->all();
            return error_return(implode(',', $errorData), CodeType::FORM_VALIDATOR_ERROR, $errorData);
        }

        try {
            (new GroupOrderFacade())->groupOrderShipping($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success');
    }
}
