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
        return app(Snowflake::class)->nextId();
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
