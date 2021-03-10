<?php
namespace App\Http\Controllers\PayNotify\GroupOrder;


use Lyndon\Logger\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseAction;
use App\Services\Orders\GroupOrder\UpdateGroupOrderService;

/**
 * Class WxPayNotify
 * @package App\Http\Controllers\PayNotify\GroupOrder
 */
class WxPayNotify extends BaseAction
{
    public function allowMethod()
    {
        return self::METHOD_POST;
    }

    public function onRun(Request $request)
    {
        $param = $request->only('trade_no', 'paid');

        try {
            if (! empty($param['paid'])) {
                // 支付完成
                (new UpdateGroupOrderService($param['trade_no']))->payComplete();
            } else {
                // 支付失败
                (new UpdateGroupOrderService($param['trade_no']))->payFail();

                Log::filename('WxPayNotify')->info('WxPayNotify', ['param' => $request->all()]);
            }
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success');
    }
}
