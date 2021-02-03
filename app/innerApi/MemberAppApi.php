<?php
namespace App\innerApi;


use Lyndon\Traits\Singleton;

/**
 * Class MemberAppApi
 * @package App\innerApi
 */
class MemberAppApi extends BaseAppApi
{
    use Singleton;

    protected $arriveName = 'member';

    /**
     * 获取用户收获地址
     *
     * @param int $userId
     * @param int $userAddressId
     * @return mixed
     * @throws \Exception
     */
    public function getUserAddressInfo($userId, $userAddressId)
    {
        $userId = hash_ids_encode($userId);
        $userAddressId = hash_ids_encode($userAddressId);

        $result = $this->get(
            'response-api/user/get-user-address',
            [
                'user_id' => $userId,
                'user_address_id' => $userAddressId,
            ]
        );

        if (! $result['status']) {
            throw new \Exception($result['message'], $result['code']);
        }

        return $result['data'];
    }
}
