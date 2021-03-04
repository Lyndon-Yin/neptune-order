<?php
namespace App\Services\Orders\GroupOrder;


use App\Services\Orders\BaseCore\OrderDetailService;
use App\Traits\Orders\OrderMailing\DetailOrderMailingTrait;

/**
 * Class GroupOrderDetailService
 * @package App\Services\Orders\GroupOrder
 */
class GroupOrderDetailService extends OrderDetailService
{
    use DetailOrderMailingTrait;

    protected $orderMailing = [];
}
