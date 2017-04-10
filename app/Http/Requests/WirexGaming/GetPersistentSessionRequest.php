<?php

namespace App\Http\WirexGaming;

use Illuminate\Http\Request;

/**
 * Class GetPersistentSessionRequest
 * @package App\Http\WirexGaming
 */
class GetPersistentSessionRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remotePersistentSessionId' => 'bail|required|alpha_num',
            'remotePersistentSessionMagic' => 'bail|required|numeric',
            'partyOriginatingUid' => 'bail|required|numeric',
            'previousContextId' => 'bail|required|numeric',
        ];
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authorizeUser(Request $request)
    {
        return true;
    }
}
