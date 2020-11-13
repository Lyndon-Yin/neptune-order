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
