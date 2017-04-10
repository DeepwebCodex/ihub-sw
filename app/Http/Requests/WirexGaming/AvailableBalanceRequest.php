<?php

namespace App\Http\WirexGaming;

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
        return [
            'partyOriginatingUid' => 'bail|required|numeric'
        ];
    }
}
