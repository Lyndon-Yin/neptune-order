<?php
namespace App\Http\Controllers\GroupOrder\UserOrder;


use Illuminate\Http\Request;
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

    public function onRun(Request $request)
    {
        $param = $request->only('user_id', 'alpha_id', 'alpha_group_id', 'alpha_batch_id', 'buy_entities', 'delivery_type',
            'user_address_id');

        try {
            (new GroupOrderFacade())->createGroupOrder($param);
        } catch (\Exception $e) {
            return error_return($e->getMessage());
        }

        return success_return('success', 200);
    }
}
