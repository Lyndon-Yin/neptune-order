<?php
namespace App\Services\Orders\GroupOrder;


use App\Services\Orders\BaseCore\OrderDetailService;
use App\Traits\Orders\OrderMailing\DetailOrderMailingTrait;
use App\Traits\Orders\OrderPayment\DetailOrderPaymentTrait;

/**
 * Class GroupOrderDetailService
 * @package App\Services\Orders\GroupOrder
 */
class GroupOrderDetailService extends OrderDetailService
{
    use DetailOrderMailingTrait, DetailOrderPaymentTrait;
}
