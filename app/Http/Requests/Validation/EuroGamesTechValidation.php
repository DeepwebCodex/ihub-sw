<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Components\Integrations\EuroGamesTech\DefenceCode;
use App\Components\Integrations\EuroGamesTech\StatusCode;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EuroGamesTechValidation
{
    /**
     * @var DefenceCode
     */
    private $defenceCode;

    public function __construct()
    {
        $this->defenceCode = new DefenceCode();
    }

    public function checkDefenceCode($attribute, $value, $parameters, $validator){
        if(!($request = $this->getRequest())){
            return false;
        }

        if($this->defenceCode->isUsed($value)){
            throw new ApiHttpException(400, "Defence code is deactivated", ['code' => StatusCode::EXPIRED]);
        }

        $userId = $request->input('PlayerId');
        $currency = EgtHelper::getCurrencyFromPortalCode($request->input('PortalCode'));

        if($this->defenceCode->isCorrect($value, $userId, $currency)){
            $this->defenceCode->setUsed($value);
            return true;
        }

        throw new ApiHttpException(400, "Defence code is wrong", ['code' => StatusCode::EXPIRED]);
    }

    public function checkExpirationTime($attribute, $value, $parameters, $validator){
        if($this->defenceCode->isExpired($value)){
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