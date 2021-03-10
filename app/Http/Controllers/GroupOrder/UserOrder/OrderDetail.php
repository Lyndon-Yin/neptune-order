<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
use Lyndon\Constant\CodeType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\OrderFacades\GroupOrderFacade;

/**
 * Class OrderDetail
 * @package App\Http\Controllers\GroupOrder\UserOrder
 */
class OrderDetail extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_GET;
    }

    /**
     * @api {get} /group-order/user-order/order-detail
     *
     * @apiVersion 1.0.0
     * @apiGroup 用户团购订单
     *
     * @apiName UserOrder/OrderDetail
     * @apiDeprecated 用户订单详情
     *
     * @apiParam {String} order_id 订单ID
     * @apiParam {String} user_id 用户ID
     *
     * @apiParamExample {json} Request-Example:
     * {
     *    "order_id": 52625394293014528
     *    "user_id": "WGqb2n"
     * }
     *
     * @apiSuccess {Boolean} status 状态码true
     * @apiSuccess {Number} code 具体状态码200等
     * @apiSuccess {String} message 状态信息提示
     * @apiSuccess {Object[]} data 返回结果集
     *
     * @apiSuccessExample Success-Response:
     * {
     *    "status": true,
     *    "code": 200,
     *    "message": "success",
     *    "data": [{
     *       "id": 47918707397099520,
     *       "merchant_id": "WGqb2n",
     *       "user_id": "WGqb2n",
     *       "order_category": 0, // 订单分类，0：未知分类，1：团购订单
     *       "order_source": 0,   // 订单来源，0：未知来源，1：微信小程序，2：网页
     *       "total_amount": "44.33",   // 订单总金额
     *       "discount_amount": "0.00", // 订单总折扣金额
     *       "shipping_amount": "0.00", // 配送费
     *       "payment_amount": "44.33", // 支付金额（总金额-折扣金额）
     *       "delivery_type": 3, // 1:邮寄到家,2:自提点自提,3:快递到自提点，团长配送到家
     *       "order_status": 0,  // 0:未支付,9:等待支付完成,20:已支付,60:已收货
     *       "order_remark": "",
     *       "order_time": null,
     *       "group_buy": {
     *          "group_id": "w27W2V",  // 团购模板ID
     *          "group_batch_id": "xGw832", // 团购模板批次ID
     *          "alpha_id": "WGqb2n", // 团长ID
     *          "alpha_group_id": "OGvNGR", // 团长开团ID
     *          "alpha_batch_id": "grJWGw", // 团长开团批次ID
     *          "alpha_group_name": "开团名称", // 团长开团名称
     *          "arrival_date": "2021-12-05", // 到货日期
     *          "arrival_time": "09:18~13:54" // 到货时间
     *       },
     *       "order_items": [{
     *          "id": "QGeB2b",
     *          "goods_name": "iphone 11 128G 墨绿",
     *          "entity_img": "klsfood.oss-cn-beijing.aliyuncs.com/ttq/images/20210105/5ff40db6b9315.jpeg",
     *          "entity_price": "11.11",
     *          "spec_name": {
     *             "颜色": "黄色",
     *             "尺寸": "32"
     *          },
     *          "buy_quantity": 2,
     *          "item_total_amount": "22.22",
     *          "item_discount_amount": "0.00",
     *          "item_payment_amount": "22.22"
     *       }, {
     *          "id": "O2Ya2z",
     *          "goods_name": "iphone 11 128G 墨绿",
     *          "entity_img": "klsfood.oss-cn-beijing.aliyuncs.com/ttq/images/20210105/5ff40db6b9315.jpeg",
     *          "entity_price": "22.11",
     *          "spec_name": [],
     *          "buy_quantity": 1,
     *          "item_total_amount": "22.11",
     *          "item_discount_amount": "0.00",
     *          "item_payment_amount": "22.11"
     *       }],
     *       "order_payment": {
     *          "payment_type": "",
     *          "payment_type_amount": 0,
     *          "account_balance_amount": 0,
     *          "payment_time": null
     *       },
     *       "order_mailing": {
     *          "consignee_name": "ddd",
     *          "consignee_phone": "18655752236",
     *          "shipping_address": "安徽省合肥市蜀山区qwer",
     *          "shipping_no": "",
     *          "shipping_time": null
     *       },
     *       "order_mailing_home": {
     *          "consignee_name": "大兄弟",
     *          "consignee_phone": "18110932720",
     *          "shipping_address": "安徽省合肥市蜀山区qwer"
     *       }
     *    }]
     * }
     *
     * @apiUse ErrorReturn
     */
    public function onRun(Request $request)
    {
        $param = $request->only('user_id', 'order_id');
        $validate = Validator::make(
            $param,
            [
                'order_id' => 'required',
                'user_id'  => 'required'
            ], [
                'order_id.required' => '订单ID不能为空',
                'user_id.required'  => '用户ID不能为空'
            ]
        );
        if ($validate->fails()) {
            $errorData = $validate->errors()->all();
            return error_return(implode(',', $errorData), CodeType::FORM_VALIDATOR_ERROR, $errorData);
        }

        try {
            $result = (new GroupOrderFacade())->groupOrderDetail($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success', 200, $result);
    }
}