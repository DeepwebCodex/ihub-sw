<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/7/16
 * Time: 11:08 AM
 */

namespace App\Http\Requests\Validation;

/**
 * Class GlobalValidation
 * @package App\Http\Requests\Validation
 */
class GlobalValidation
{
    public static function CheckSessionToken($attribute, $value, $parameters, $validator)
    {
        return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $value) > 0;
    }
}
