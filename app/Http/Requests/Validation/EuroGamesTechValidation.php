<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Components\Integrations\EuroGamesTech\DefenceCode;
use App\Components\Integrations\EuroGamesTech\StatusCode;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EuroGamesTechValidation
{
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