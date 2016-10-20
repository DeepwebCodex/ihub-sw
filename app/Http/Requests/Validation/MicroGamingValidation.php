<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class MicroGamingValidation
{
    public static function validateToken($attribute, $value, $parameters, $validator)
    {
        $message = null;

        if(!$value){
            throw new ApiHttpException(400, "Token field is empty", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        list($remoteSessionId, $time, $hash) = MicroGamingHelper::parseToken($value);

        if(!GlobalValidation::CheckSessionToken(null, $remoteSessionId, null, null))
        {
            throw new ApiHttpException(400, "Invalid session", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        if(is_numeric($time) === false)
        {
            throw new ApiHttpException(400, "Invalid time", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        return true;
    }

    public static function validateTime($attribute, $value, $parameters, $validator)
    {
        list($remoteSessionId, $time, $hash) = MicroGamingHelper::parseToken($value);

        $time_t = time() - $time;

        if ($time_t >= config('integrations.microgaming.time_expire'))
        {
            throw new ApiHttpException(400, "Player token expired {$time_t}", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        return true;
    }

    public static function validatePlayType($attribute, $value, $parameters, $validator)
    {
        if(!in_array($value, ['bet', 'win', 'refund']))
        {
            throw new ApiHttpException(501, "Playtype '{$value}' is not implemented", CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD));
        }

        return true;
    }
}