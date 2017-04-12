<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\BetGames\ApiMethod;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\StatusCode;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class BetGamesValidation
{
    private $time_limit = 60;
    private $token_length = 10;

    public function checkTime($attribute, $value, $parameters, $validator):bool
    {
        if ((time() - $value) > $this->time_limit) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::TIME,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
                'partnerId' => Request::getFacadeRoot()->partner_id,
                'cashdeskId' => Request::getFacadeRoot()->cashdesk_id,
            ]);
        }

        return true;
    }

    public function checkMethod($attribute, $value, $parameters, $validator):bool
    {
        $apiMethod = new ApiMethod($value);
        if (!$apiMethod->get()) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::UNKNOWN,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
                'partnerId' => Request::getFacadeRoot()->partner_id,
                'cashdeskId' => Request::getFacadeRoot()->cashdesk_id,
            ]);
        }

        return true;
    }

    public function checkToken($attribute, $value, $parameters, $validator):bool
    {
        $hasLetters = (bool)preg_match('/[A-Za-z]/', $value);
        $hasDigits = (bool)preg_match('/[0-9]/', $value);
        $hasEnoughLength = strlen($value) >= $this->token_length;

        if($hasLetters && $hasDigits && $hasEnoughLength){
            return true;
        }

        throw new ApiHttpException(400, null, [
            'code' => StatusCode::TOKEN,
            'method' => Request::getFacadeRoot()->method,
            'token' => Request::getFacadeRoot()->token,
            'partnerId' => Request::getFacadeRoot()->partner_id,
            'cashdeskId' => Request::getFacadeRoot()->cashdesk_id,
        ]);
    }
}