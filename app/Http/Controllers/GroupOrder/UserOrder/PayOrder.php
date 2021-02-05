<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\OrderFacades\GroupOrderFacade;

/**
 * Class PayOrder
 * @package App\Http\Controllers\GroupOrder\UserOrder
 */
class PayOrder extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_POST;
    }

    /**
     * @api {post} /group-order/user-order/pay-order
     *
     * @apiVersion 1.0.0
     * @apiGroup 团购订单
     *
     * @apiName UserOrder/PayOrder
     * @apiDeprecated 团购订单支付
     *
     * @apiParam {String} order_id 订单ID
     *
     * @apiParamExample {json} Request-Example:
     * {
     *    "order_id": "47555647020990464",
     * }
     *
     * @apiSuccess {Boolean} status 状态码true
     * @apiSuccess {Number} code 具体状态码200等
     * @apiSuccess {String} message 状态信息提示
     * @apiSuccess {Object} data 返回结果集
     *
     * @apiSuccessExample Success-Response:
     * {
     *    "status": true,
     *    "code": 200,
     *    "message": "success",
     *    "data": {
     *       "appId": "wx67127c10ce9c598c",
     *       "package": "prepay_id=wx18173859175438a7970fbb113230f20000",
     *       "nonceStr": "c3de54607cd3c61a07c7985751b38694",
     *       "signType": "MD5",
     *       "paySign": "463624715A393CCEC689DEBC27F44FB2",
     *       "timestamp": "1613641139"
     *    }
     * }
     *
     * @apiUse ErrorReturn
     */
    public function onRun(Request $request)
    {
        $param = $request->only('order_id');

        try {
            $result = (new GroupOrderFacade())->payGroupOrder($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success', 200, $result);
    }
}
