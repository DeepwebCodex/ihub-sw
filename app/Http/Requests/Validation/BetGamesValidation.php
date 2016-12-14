<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\BetGames\ApiMethod;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\Error;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class BetGamesValidation
{
    private $signature;
    private $time_limit = 60;

    public function checkSignature($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $all = $request->all();
        unset($all['signature']);
        $this->signature = new Signature($all);
        if ($this->signature->isWrong($value)) {
            throw new ApiHttpException(400, null, [
                'code' => Error::SIGNATURE,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
            ]);
        }

        return true;
    }

    public function checkTime($attribute, $value, $parameters, $validator):bool
    {
        if ((time() - $value) > $this->time_limit) {
            throw new ApiHttpException(400, null, [
                'code' => Error::TIME,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
            ]);
        }

        return true;
    }

    public function checkToken($attribute, $value, $parameters, $validator):bool
    {
        if ('ping' == Request::getFacadeRoot()->method) {
            return ($value == '-');
        }
        if (!app('GameSession')->get('user_id')) {
            throw new ApiHttpException(400, null, [
                'code' => Error::TOKEN,
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
                'code' => Error::SIGNATURE,
                'method' => Request::getFacadeRoot()->method,
                'token' => Request::getFacadeRoot()->token,
            ]);
        }

        return true;
    }
}