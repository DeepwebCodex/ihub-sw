<?php

namespace App\Http\Requests\BetGames;

/**
 * Class WinRequest
 * @package App\Http\Requests\BetGames
 */
class OnlineRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see BetGamesValidation::checkToken
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'token' => 'bail|required|string|check_token',
        ]);
    }
}
