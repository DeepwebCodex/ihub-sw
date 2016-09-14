<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EuroGamesTechValidation
{
    public static function checkDefenceCode($attribute, $value, $parameters, $validator){
        if(!($request = self::getRequest())){
            return false;
        }

        $userId = $request->input('PlayerId');
        $currency = substr($request->input('PortalCode'), -3);

        list($hash, $time) = explode('-', $value);

        if($value == EgtHelper::generateDefenceCode($userId, $currency, $time)){
            return true;
        }

        throw new ApiHttpException(400, "Defence code expired", ['code' => 3100]);
    }

    public static function checkExpirationTime($attribute, $value, $parameters, $validator){
        list($hash, $time) = explode('-', $value);

        if((time() - $time) > 120){
            throw new ApiHttpException(400, "Defence code expired", ['code' => 3100]);
        }

        return true;
    }

    /**
     * @return \Illuminate\Http\Request
     */
    private static function getRequest(){
        return Request::getFacadeRoot();
    }
}