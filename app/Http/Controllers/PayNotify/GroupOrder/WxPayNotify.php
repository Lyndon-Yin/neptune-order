<?php
namespace App\Http\Controllers\PayNotify\GroupOrder;


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
        $param = $request->only('order_id');

        try {
            (new UpdateGroupOrderService($param['order_id']))->payComplete();
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success');
    }
}
