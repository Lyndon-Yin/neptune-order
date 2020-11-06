<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => [
    ]
], function ($router) {
    $router->any('{slug?}', function (Request $request) {
        return \Lyndon\Route\Path4Router::route($request);
    })->where('slug', '(.*)?');
});
