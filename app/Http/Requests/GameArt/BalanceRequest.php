<?php

namespace App\Http\Requests\GameArt;

class BalanceRequest extends BaseGameArtRequest
{
    public function messages()
    {
        return [];
    }

    public function rules()
    {
        return [
            'action'        => 'bail|string|required',
            'remote_id'     => 'bail|integer|required',
            'remote_data'   => 'bail|string',
            'session_id'    => 'bail|string',
            'key'           => 'bail|string|required|validate_key'
        ];
    }
}