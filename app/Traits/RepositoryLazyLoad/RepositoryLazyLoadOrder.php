<?php
namespace App\Traits\RepositoryLazyLoad;


use App\Repositories\Orders\OrderDiscountRepository;
use App\Repositories\Orders\OrderItemRepository;
use App\Repositories\Orders\OrderMailingRepository;
use App\Repositories\Orders\OrderPaymentRepository;
use App\Repositories\Orders\OrderRepository;

/**
 * Trait RepositoryLazyLoadGoods
 * @package App\Traits\RepositoryLazyLoad
 *
 * @property OrderDiscountRepository $orderDiscRepo
 * @property OrderItemRepository $orderItemRepo
 * @property OrderMailingRepository $orderMailRepo
 * @property OrderPaymentRepository $orderPayRepo
 * @property OrderRepository $orderRepo
 *
 */
trait RepositoryLazyLoadOrder
{
    use BaseRepositoryLazyLoad;

    /**
     * 允许注入的repository名称
     *
     * @var array
     */
    protected $repository = [
        'orderDiscRepo' => OrderDiscountRepository::class,
        'orderItemRepo' => OrderItemRepository::class,
        'orderMailRepo' => OrderMailingRepository::class,
        'orderPayRepo'  => OrderPaymentRepository::class,
        'orderRepo'     => OrderRepository::class,
    ];
}
