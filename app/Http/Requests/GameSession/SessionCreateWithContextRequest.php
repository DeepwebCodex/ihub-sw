<?php

namespace App\Http\Requests\GameSession;

/**
 * Class SessionCreateWithContextRequest
 * @package App\Http\Requests\GameSession
 */
class SessionCreateWithContextRequest extends BaseGameSessionRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'bail|required|numeric',
            'partner_id' => 'bail|numeric',
            'game_id' => 'bail|required',
            'currency' => 'bail|required|string',
            'cashdesk_id' => 'bail|numeric',
            'context' => 'bail|required'
        ];
    }
}
