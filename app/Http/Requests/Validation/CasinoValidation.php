<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\Casino\CasinoHelper;
use Illuminate\Support\Facades\Request;

class CasinoValidation
{
    public static function CheckSignature($attribute, $value, $parameters, $validator){
        if(!($request = self::getRequest())){
            return false;
        }

        $requestData = $request->all();

        if($value == CasinoHelper::generateActionSignature($requestData)){
            return true;
        }

        return false;
    }

    public static function CheckTime($attribute, $value, $parameters, $validator){
        if((time() - $value) <= 360){
            return true;
        }

        return false;
    }

    /**
     * @return \Illuminate\Http\Request
     */
    private static function getRequest(){
        return Request::getFacadeRoot();
    }
}