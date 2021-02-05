<?php
namespace App\innerApi;


use Lyndon\Traits\Singleton;

/**
 * Class PaymentAppApi
 * @package App\innerApi
 */
class PaymentAppApi extends BaseAppApi
{
    use Singleton;

    protected $arriveName = 'payment';

    /**
     * 微信支付
     *
     * @param int $userId
     * @param int $merchantId
     * @param string $tradeNo
     * @param string $totalFee
     * @param string $notifyUrl
     * @return mixed
     * @throws \Exception
     */
    public function wxPay($userId, $merchantId, $tradeNo, $totalFee, $notifyUrl)
    {
        $results = $this->post(
            'payment/wechat-pay/pay',
            [
                'user_id'     => $userId,
                'merchant_id' => $merchantId,
                'trade_no'    => $tradeNo,
                'total_fee'   => $totalFee,
                'notify_url'  => $notifyUrl
            ]
        );

        if (! $results['status']) {
            throw new \Exception($results['message'], $results['code']);
        }

        return $results['data'];
    }
}
