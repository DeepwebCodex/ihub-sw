<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Components\Integrations\EuroGamesTech\StatusCode;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EuroGamesTechValidation
{
    public function checkDefenceCode($attribute, $value, $parameters, $validator){
        if(!($request = $this->getRequest())){
            return false;
        }

        if(EgtHelper::isDefenceCodeUsed($value)){
            throw new ApiHttpException(400, "Defence code is deactivated", ['code' => StatusCode::EXPIRED]);
        }

        $userId = $request->input('PlayerId');
        $currency = substr($request->input('PortalCode'), -3);

        list($hash, $time) = explode('-', $value);

        if($value == EgtHelper::generateDefenceCode($userId, $currency, $time)){
            EgtHelper::setDefenceCodeUsed($value);
            return true;
        }

        throw new ApiHttpException(400, "Defence code expired", ['code' => StatusCode::EXPIRED]);
    }

    public function checkExpirationTime($attribute, $value, $parameters, $validator){
        list($hash, $time) = explode('-', $value);

        if((time() - $time) > EgtHelper::DEFENCE_CODE_EXPIRATION_TIME){
            throw new ApiHttpException(400, "Defence code expired", ['code' => StatusCode::EXPIRED]);
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