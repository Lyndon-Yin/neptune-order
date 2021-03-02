<?php
namespace App\Transformers\GroupOrder;


use App\Transformers\BaseTransformer;

class GroupOrderTransformer extends BaseTransformer
{
    public function transform($param)
    {
        $param['merchant_id'] = hash_ids_encode($param['merchant_id']);
        $param['user_id']     = hash_ids_encode($param['user_id']);
        unset($param['created_at'], $param['updated_at'], $param['deleted_at']);

        if (! empty($param['order_items'])) {
            $orderItems = [];
            foreach ($param['order_items'] as $val) {
                $orderItems[] = [
                    'id' => hash_ids_encode($val['id']),
                    'goods_name'   => $val['goods_name'],
                    'entity_img'   => $val['entity_img'],
                    'entity_price' => $val['entity_price'],
                    'spec_name'    => empty($val['entity_spec_value']) ? [] : json_decode($val['entity_spec_value'], true),
                    'buy_quantity' => $val['buy_quantity'],
                    'item_total_amount'    => $val['item_total_amount'],
                    'item_discount_amount' => $val['item_discount_amount'],
                    'item_payment_amount'  => $val['item_payment_amount']
                ];
            }

            $param['order_items'] = $orderItems;
            unset($orderItems);
        }

        return $param;
    }
}
