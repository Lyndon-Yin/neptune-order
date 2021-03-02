<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
use Lyndon\Constant\CodeType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\GroupOrder\BaseOrderAction;
use App\Services\Orders\GroupOrder\GroupOrderListService;

/**
 * Class OrderList
 * @package App\Http\Controllers\GroupOrder\UserOrder
 */
class OrderList extends BaseOrderAction
{
    public function allowMethod()
    {
        return self::METHOD_GET;
    }

    public function onRun(Request $request)
    {
        $param = $request->only('user_id', 'page_size');
        $validate = Validator::make(
            $param,
            ['user_id' => 'required'],
            ['user_id.required' => '用户ID不能为空']
        );
        if ($validate->fails()) {
            $errorData = $validate->errors()->all();
            return error_return(implode(',', $errorData), CodeType::FORM_VALIDATOR_ERROR, $errorData);
        }

        try {
            $result = (new GroupOrderListService())
                ->userOrderList($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success', 200, $result);
    }
}
