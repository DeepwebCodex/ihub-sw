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
        $prefix = $this->getRequestDataPrefix();
        return [
            $prefix . 'remotePersistentSessionId' => 'bail|required|numeric',
            $prefix . 'remotePersistentSessionMagic' => 'bail|required|alpha_num',
            $prefix . 'partyOriginatingUid' => 'bail|required|numeric',
            $prefix . 'previousContextId' => 'bail|required|numeric',
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

    /**
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return true;
    }
}
