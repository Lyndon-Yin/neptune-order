<?php
namespace App\Traits\Orders\OrderMailing;


use Illuminate\Support\Facades\DB;

/**
 * Trait CreateOrderMailingTrait
 * @package App\Traits\Orders\OrderMailing
 */
trait CreateOrderMailingTrait
{
    use BaseOrderMailingTrait;

    // 用户地址ID
    private $userAddressId = 0;

    /**
     * 添加邮寄信息
     *
     * @param array $param
     * @return $this
     */
    public function pushMailingInfo(array $param)
    {
        $this->consigneeName   = empty($param['consignee_name']) ? '' : trim($param['consignee_name']);
        $this->consigneePhone  = empty($param['consignee_phone']) ? '' : trim($param['consignee_phone']);
        $this->shippingAddress = empty($param['shipping_address']) ? '' : trim($param['shipping_address']);
        $this->pointLng        = isset($param['point_lng']) ? $param['point_lng'] : null;
        $this->pointLat        = isset($param['point_lat']) ? $param['point_lat'] : null;

        return $this;
    }

    /**
     * 添加用户地址，自动查询地址信息
     *
     * @param int $userAddressId
     * @return $this
     */
    public function pushUserAddressId(int $userAddressId)
    {
        $this->userAddressId = intval($userAddressId);

        return $this;
    }

    /**
     * 添加od_order_mailing表
     *
     * @throws \Lyndon\Exceptions\ModelException
     * @throws \Exception
     */
    protected function createOrderMailingTable()
    {
        // 不存在配送方式，不添加邮寄信息
        if (empty($this->deliveryType)) {
            return;
        }

        if (empty($this->orderId)) {
            throw new \Exception('订单ID不能为空');
        }

        $temp = [
            'order_id'         => $this->orderId,
            'consignee_name'   => $this->consigneeName,
            'consignee_phone'  => $this->consigneePhone,
            'shipping_address' => $this->shippingAddress,
        ];
        if (! is_null($this->pointLat) && ! is_null($this->pointLng)) {
            $temp['point'] = DB::raw("GeomFromText('POINT(" . $this->pointLng . " " . $this->pointLat . ")')");
        }

        $this->orderMailRepo->addRepoRow($temp);
    }
}
