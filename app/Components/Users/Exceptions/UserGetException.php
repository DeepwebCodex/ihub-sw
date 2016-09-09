<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 12:27 PM
 */

namespace App\Components\Users\Exceptions;


use App\Exceptions\Api\ApiHttpException;

class UserGetException extends ApiHttpException
{
    public function __construct($httpCode, $message = "", $code = null, \Exception $previous = null) {
        parent::__construct($httpCode, $message, ($code !== null) ? ['code' => $code] : [], $previous);
    }
}