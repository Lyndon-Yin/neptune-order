<?php
namespace App\Services\Orders\NormalOrder;


use App\Traits\Orders\OrderPaymentTrait;
use App\Services\Orders\BaseCore\OrderUpdateService;
use App\Traits\Orders\OrderMailing\UpdateOrderMailingTrait;

/**
 * Class UpdateNormalOrderService
 * @package App\Services\Orders\NormalOrder
 */
class UpdateNormalOrderService extends OrderUpdateService
{
    use UpdateOrderMailingTrait, OrderPaymentTrait;
}
