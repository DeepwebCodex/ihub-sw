<?php

namespace App\Http\Requests\MicroGaming;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class PlayRequest extends BaseMicroGamingRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'methodcall.call.token.validate_token' => 'Invalid token',
            'methodcall.call.token.validate_play_type'  => 'Invalid playtype',
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'methodcall.system'     => 'bail|required|string|in:casino',
            'methodcall.call.seq'   => 'bail|required|string',
            'methodcall.call.token' => 'bail|required|string|validate_token',
            'methodcall.call.playtype' => 'bail|required|string|validate_play_type',
            'methodcall.call.gameid' => 'bail|required|integer',
            'methodcall.call.actionid' => 'bail|required|integer',
            'methodcall.call.amount' => 'bail|required|integer',
            'methodcall.call.gamereference' => 'bail|required|string',
        ];
    }
}
