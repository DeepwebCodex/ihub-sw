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

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public static function validateDateValue($attribute, $value, $parameters, $validator)
    {
        if (is_numeric($value)) {
            $date = date_create_from_format('U', $value);
        } else {
            $date = date_create_from_format('Y-m-d H:i:s', $value);
        }
        if ($date) {
            $dateTimestamp = date_timestamp_get($date);
            $leftBorderDate = date_create_from_format('Y-m-d H:i:s', '1970-01-01 00:00:00');
            $rightBorderDate = date_create_from_format('Y-m-d H:i:s', '3000-12-31 00:00:00');

            return ($dateTimestamp > date_timestamp_get($leftBorderDate)
                && $dateTimestamp < date_timestamp_get($rightBorderDate));
        }
        return false;
    }
}
