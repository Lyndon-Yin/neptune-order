<?php
/**
 * @apiDefine ErrorReturn
 *
 * @apiError {Boolean} status 状态false
 * @apiError {Number} code 错误码400等
 * @apiError {String} message 错误提示
 * @apiError {array} data 返回结果集
 *
 * @apiErrorExample Error-Response:
 * {
 *    "status": false,
 *    "code": 400,
 *    "message": "网路异常",
 *    "data": []
 * }
 */
namespace App\Http\Controllers;


use Lyndon\Route\AbstractAction;

/**
 * Class BaseAction
 * @package App\Http\Controllers
 */
abstract class BaseAction extends AbstractAction
{

}
