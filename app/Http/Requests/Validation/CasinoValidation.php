<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\Casino\CasinoHelper;
use Illuminate\Support\Facades\Request;

class CasinoValidation
{
    public function CheckSignature($attribute, $value, $parameters, $validator){
        if(!($request = $this->getRequest())){
            return false;
        }

        $requestData = $request->all();

        if($value == CasinoHelper::generateActionSignature($requestData)){
            return true;
        }

        return false;
    }

    public function CheckTime($attribute, $value, $parameters, $validator){
        if((time() - $value) <= 360){
            return true;
        }

        return false;
    }

    public function CheckAmount($attribute, $value, $parameters, $validator){
        if($value <= 0){
            return false;
        }

        return true;
    }

    /**
     * @return \Illuminate\Http\Request
     */
    public function getRequest(){
        return Request::getFacadeRoot();
    }
}