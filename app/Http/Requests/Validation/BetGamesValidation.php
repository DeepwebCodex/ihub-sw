<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\Status;
use App\Components\Integrations\BetGames\Token;
use App\Components\Users\IntegrationUser;
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

        $this->signature = new Signature($request->all());

        if ($this->signature->isWrong($value)) {
            $status = new Status('wrong_signature');
            throw new ApiHttpException(400, $status->getMessage(), ['code' => $status->getCode()]);
        }

        return false;
    }

    public function checkTime($attribute, $value, $parameters, $validator):bool
    {
        if ((time() - $value) > $this->time_limit) {
            $status = new Status('time_expired');
            throw new ApiHttpException(400, $status->getMessage(), ['code' => $status->getCode()]);
        }

        return true;
    }

    public function checkToken($attribute, $value, $parameters, $validator):bool
    {
        return true;
        $token = new Token($value);

        $user = IntegrationUser::get($token->getUserId(), config('integrations.betGames.service_id'), 'betGames');
        if ($token->isExpired() || $token->isWrongCurrency($user->getCurrency())) {
            $status = new Status('wrong_token');
            throw new ApiHttpException(400, $status->getMessage(), ['code' => $status->getCode()]);
        } else {
            $token->refresh();
        }


        return true;
    }
}