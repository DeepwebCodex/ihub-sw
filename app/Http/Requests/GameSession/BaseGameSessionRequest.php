<?php

namespace App\Http\Requests\GameSession;

use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

/**
 * Class BaseGameSessionRequest
 * @package App\Http\Requests\GameSession
 */
class BaseGameSessionRequest extends ApiRequest
{
    /**
     * @return bool
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function authorize()
    {
        $config = config('auth.api.game_session');
        $login = $config['login'];
        $password = $config['password'];

        if (empty($login) || empty($password)) {
            throw new ApiHttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Configuration error');
        }
        return Request::getUser() === $login && Request::getPassword() === $password;
    }
}
