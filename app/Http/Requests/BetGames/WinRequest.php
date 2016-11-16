<?php

namespace App\Http\Requests\BetGames;
use App\Http\Requests\Validation\BetGamesValidation;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class WinRequest extends BaseRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'DefenceCode.check_defence_code' => 'Invalid defence code',
            'DefenceCode.check_expiration_time'  => 'Expired defence code',
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /**
         * @see BetGamesValidation::checkToken
         */
        return array_merge(parent::rules(), [
            'token' => 'bail|required|string|check_token',
        ]);
    }
}
