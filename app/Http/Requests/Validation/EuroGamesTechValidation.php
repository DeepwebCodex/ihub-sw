<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EuroGamesTechValidation
{
    public function checkDefenceCode($attribute, $value, $parameters, $validator){
        if(!($request = $this->getRequest())){
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

    public function checkExpirationTime($attribute, $value, $parameters, $validator){
        list($hash, $time) = explode('-', $value);

        if((time() - $time) > 120){
            throw new ApiHttpException(400, "Defence code expired", ['code' => 3100]);
        }

        return true;
    }

    public function validateDepositReason($attribute, $value, $parameters, $validator){
        if(!in_array($value, ['ROUND_END', 'ROUND_CANCEL', 'JACKPOT_END']))
        {
            return false;
        }

        return true;
    }

    public function validateWithdrawReason($attribute, $value, $parameters, $validator){
        if($value != 'ROUND_BEGIN')
        {
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