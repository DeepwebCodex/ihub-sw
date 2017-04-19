<?php

namespace App\Http\WirexGaming;

use Illuminate\Http\Request;

/**
 * Class AvailableBalanceRequest
 * @package App\Http\WirexGaming
 */
class AvailableBalanceRequest extends BaseRequest
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
            $prefix . 'partyOriginatingUId' => 'bail|required|numeric'
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
