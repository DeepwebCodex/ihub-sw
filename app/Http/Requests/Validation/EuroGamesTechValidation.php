<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\EuroGamesTech\StatusCode;
use App\Components\Integrations\GameSession\TokenControl\TokenControl;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EuroGamesTechValidation
{
    /**
     * @var TokenControl
    */
    protected $tokenControl;

    public function __construct()
    {
        $this->tokenControl = new TokenControl('euro_games_tech', 2);
    }

    public function validateDefenceCode($attribute, $value, $parameters, $validator)
    {
        if($this->tokenControl->isUsed($value))
        {
            throw new ApiHttpException(400, "Defence code is deactivated", ['code' => StatusCode::EXPIRED]);
        }

        $this->tokenControl->setUsed($value);

        return true;
    }

    public function validateDepositReason($attribute, $value, $parameters, $validator)
    {
        if(!in_array($value, ['ROUND_END', 'ROUND_CANCEL', 'JACKPOT_END']))
        {
            return false;
        }

        return true;
    }

    public function validateWithdrawReason($attribute, $value, $parameters, $validator)
    {
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