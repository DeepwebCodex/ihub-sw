<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations;


class CodeMappingBase
{
    const SERVER_ERROR      = 'server_error';
    const SUCCESS           = 'success';
    const NO_MONEY          = 'no_money';
    const INVALID_CURRENCY  = 'invalid_currency';
    const USER_NOT_FOUND    = 'user_not_found';
    const INVALID_RESPONSE  = 'invalid_response';
    const INVALID_TOKEN     = 'invalid_token';
    const INVALID_USER_ID   = 'invalid_user_id';
    const INVALID_RESULT    = 'invalid_result';
    const UNKNOWN_METHOD    = 'unknown_method';
    const TIME_EXPIRED      = 'time_expired';
    const INVALID_SIGNATURE = 'invalid_signature';
    const INVALID_SUM       = 'invalid_sum';
    const INVALID_SERVICE   = 'invalid_service';
    const INVALID_WALLET    = 'invalid_wallet';

    public static function getMapping(){
        return [];
    }

    public static function getAttributeMessages(string $attribute = null){
        $mapping  = static::getMapping();

        $result = [];

        if($mapping) {
            foreach ($mapping as $code => $item) {
                if ($item['attribute']) {
                    $result[$item['attribute']] = [
                        'message' => $item['message'],
                        'code' => $code
                    ];
                }
            }
        }

        if($attribute){
            return array_get($result, $attribute, []);
        }

        return $result;
    }

    public static function getByErrorCode($responseCode){
        $mapping  = static::getMapping();

        $result = [];

        if($mapping) {
            foreach ($mapping as $code => $item) {
                $item['map'][] = $code;
                if (isset($item['map']) && !empty($item['map']) && is_array($item['map']) && in_array($responseCode,
                        $item['map'])
                ) {
                    return [
                        'message' => $item['message'],
                        'code' => $code
                    ];
                }
            }

            foreach ($mapping as $code => $item) {
                if (isset($item['default']) && $item['default'] == true) {
                    return [
                        'message' => $item['message'],
                        'code' => $code
                    ];
                }
            }
        }

        return $result;
    }

    public static function getByMeaning(string $meaning){
        $mapping  = static::getMapping();

        $result = [];

        if($mapping) {
            foreach ($mapping as $code => $item) {
                if (isset($item['meanings']) && !empty($item['meanings']) && is_array($item['meanings']) && in_array($meaning,
                        $item['meanings'])
                ) {
                    return [
                        'message' => $item['message'],
                        'code' => $code
                    ];
                }
            }
        }

        return $result;
    }
}