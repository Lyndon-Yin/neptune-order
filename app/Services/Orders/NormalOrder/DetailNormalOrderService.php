<?php
namespace App\Services\Orders\NormalOrder;


use App\Services\Orders\BaseCore\OrderDetailService;
use App\Traits\Orders\OrderMailing\DetailOrderMailingTrait;

/**
 * Class DetailNormalOrderService
 * @package App\Services\Orders\NormalOrder
 */
class DetailNormalOrderService extends OrderDetailService
{
    use DetailOrderMailingTrait;
}
