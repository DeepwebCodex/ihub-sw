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

    public function checkTime($attribute, $value, $parameters, $validator):bool
    {
        if ((time() - $value) > $this->time_limit) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::TIME,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
            ]);
        }

        return true;
    }

    public function checkMethod($attribute, $value, $parameters, $validator):bool
    {
        $apiMethod = new ApiMethod($value);
        if (!$apiMethod->get()) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::SIGNATURE,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
            ]);
        }

        return true;
    }
}