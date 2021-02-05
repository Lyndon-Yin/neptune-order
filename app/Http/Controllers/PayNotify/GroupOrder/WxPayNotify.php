<?php
namespace App\Http\Controllers\PayNotify\GroupOrder;


use App\Http\Controllers\BaseAction;
use Illuminate\Http\Request;

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
        return true;
    }
}
