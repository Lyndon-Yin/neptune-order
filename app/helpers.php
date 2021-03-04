<?php

use Lyndon\Snowflake;


if (! function_exists('order_id')) {
    /**
     * 获取订单ID
     *
     * @return mixed
     */
    function order_id()
    {
        return intval(app(Snowflake::class)->nextId());
    }
}

if (! function_exists('get_page_size')) {
    /**
     * 解析用户page_size传参
     *
     * @param array $param
     * @param int $default
     * @return int
     */
    function get_page_size($param = [], $default = 15)
    {
        if (! isset($param['page_size'])) {
            return $default;
        }

        $pageSize = intval($param['page_size']);

        if ($pageSize < 0) {
            return -1;
        } elseif ($pageSize == 0) {
            return $default;
        } else {
            return $pageSize;
        }
    }
}

if (! function_exists('is_mobile')) {
    /**
     * 验证字符串是否为手机号码
     *
     * @param string $mobile
     * @return bool
     */
    function is_mobile($mobile)
    {
        $pattern = '/^1\d{10}$/';
        if (preg_match($pattern, $mobile)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('distance')) {
    /**
     * 两个经纬度间的距离（米）
     *
     * @param float $lng1
     * @param float $lat1
     * @param float $lng2
     * @param float $lat2
     * @return float
     */
    function distance($lng1, $lat1, $lng2, $lat2)
    {
        // 地球半径（米）
        $earthRadius = 6371393;

        // 将角度转为狐度
        $radLng1 = deg2rad($lng1);
        $radLat1 = deg2rad($lat1);

        $radLng2 = deg2rad($lng2);
        $radLat2 = deg2rad($lat2);

        // 两个弧度差值
        $vLng = abs($radLng1 - $radLng2);
        $vLat = abs($radLat1 - $radLat2);

        $s = pow(sin($vLat / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($vLng / 2), 2);
        $s = 2 * $earthRadius * asin(sqrt($s));

        return round($s, 1);
    }
}
