<?php
namespace App\innerApi;


use Lyndon\Logger\Log;
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
     * @param string $body
     * @return mixed
     * @throws \Exception
     */
    public function wxPay($userId, $merchantId, $tradeNo, $totalFee, $notifyUrl, $body = '')
    {
        $param = [
            'user_id'        => $userId,
            'merchant_id'    => $merchantId,
            'trade_no'       => $tradeNo,
            'total_fee'      => $totalFee,
            'notify_url'     => $notifyUrl,
            'payment_type'   => 'WX',
            'payment_driver' => 'Js',
            'order_info'     => $body
        ];
        $results = $this->post('/payment/user-pay/prepay', $param);

        if (! $results['status']) {
            Log::filename('PaymentAppApi')->error('PaymentAppApi@wxPay', compact('param', 'results'));
            throw new \Exception($results['message'], $results['code']);
        }

        return $results['data'];
    }
}
