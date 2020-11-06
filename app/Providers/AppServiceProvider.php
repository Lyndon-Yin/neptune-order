<?php

namespace App\Providers;


use Lyndon\Logger\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 本地开发环境打印sql日志
        if (app()->environment('dev')) {
            \DB::listen(function ($query) {
                $i = 0;
                $rawSql = preg_replace_callback('/\?/', function ($matches) use ($query, &$i) {
                    $item = isset($query->bindings[$i]) ? $query->bindings[$i] : $matches[0];
                    $i++;
                    return gettype($item) == 'string' ? "'$item'" : $item;
                }, $query->sql);

                Log::filename('sql')->info('sql(' . $query->time . ')', $rawSql);
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
