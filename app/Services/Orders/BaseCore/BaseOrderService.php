<?php
namespace App\Services\Orders\BaseCore;


use App\Traits\RepositoryLazyLoad\RepositoryLazyLoadOrder;

/**
 * Class BaseOrderService
 * @package App\Services\Orders
 *
 * @property string $orderId 订单ID
 * @property int $merchantId 商家ID
 * @property int $userId 用户ID
 * @property int $orderCategory 订单分类
 * @property int $orderSource 订单来源
 * @property float $totalAmount 订单总金额
 * @property float $discountAmount 订单总折扣金额
 * @property float $shippingAmount 运费
 * @property float $paymentAmount 实付金额
 * @property int $deliveryType 订单配送类型
 * @property int $orderStatus 订单状态
 * @property string $orderTime 订单完成时间
 * @property string $createdAt 订单创建时间
 * @property array $orderItems 订单详情
 */
class BaseOrderService
{
    use RepositoryLazyLoadOrder;

    /** 订单主表od_orders字段 **/
    // 订单ID
    protected $orderId = '';

    // 商家ID
    protected $merchantId = 0;

    // 用户ID
    protected $userId = 0;

    // 订单分类
    protected $orderCategory = 0;

    // 订单来源
    protected $orderSource = 0;

    // 订单总金额
    protected $totalAmount = 0.00;

    // 订单总折扣金额
    protected $discountAmount = 0.00;

    // 运费
    protected $shippingAmount = 0.00;

    // 实付金额
    protected $paymentAmount = 0.00;

    // 订单配送类型
    protected $deliveryType = 0;

    // 订单状态
    protected $orderStatus = 0;

    // 订单完成时间
    protected $orderTime = null;

    // 订单创建时间
    protected $createdAt = null;

    /** 订单详情表od_order_items字段 **/
    protected $orderItems = [];

    /**
     * 实现私有属性只读不可写功能
     * 重写RepositoryLazyLoadOrder中懒加载功能
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (isset($this->repository[$name])) {
            // repository懒加载
            if (empty($this->$name)) {
                $className = $this->repository[$name];
                $this->$name = new $className();
            }

            return $this->$name;
        } elseif (isset($this->$name)) {
            // 私有属性只读不可写
            switch ($name) {
                case '':
                    return '';
                    break;
                default:
                    return $this->$name;
                    break;
            }
        } else {
            throw new \Exception($name . ' Undefined');
        }
    }
}
