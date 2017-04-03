<?php

namespace App\Http\Requests\BetGames;

/**
 * Class BetRequest
 * @package App\Http\Requests\BetGames
 */
class BetRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'token' => 'bail|required|string|check_token',
            'params.amount' => 'bail|required|integer|min:1',
            'params.currency' => 'bail|required|string',
            'params.bet_id' => 'bail|required|integer|min:1',
            'params.transaction_id' => 'bail|required|integer|min:1',
            'params.retrying' => 'bail|required|integer|boolean',
        ]);
    }
}
