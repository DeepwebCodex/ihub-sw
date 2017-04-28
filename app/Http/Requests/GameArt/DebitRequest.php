<?php

namespace App\Http\Requests\GameArt;

class DebitRequest extends BaseGameArtRequest
{
    public function messages()
    {
        return [];
    }

    public function rules()
    {
        return [
            'action'            => 'bail|string|required',
            'action_type'       => 'bail|string|required',
            'remote_id'         => 'bail|integer|required',
            'amount'            => 'bail|',
            'game_id'           => 'bail|integer',
            'transaction_id'    => 'bail|integer|required',
            'round_id'          => 'bail|integer',
            'remote_data'       => 'bail|string',
            'session_id'        => 'bail|string',
            'key'               => 'bail|string|required|validate_key',
        ];
    }
}