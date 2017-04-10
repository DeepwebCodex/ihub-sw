<?php

namespace App\Http\WirexGaming;

/**
 * Class AddDepositRequest
 * @package App\Http\WirexGaming
 */
class AddDepositRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'bail|required',
            'callerContextId' => 'bail|required|numeric',
            'contextId' => 'bail|required|numeric',
            'originatingPid' => 'bail|required|numeric',
            'partyOriginatingUid' => 'bail|required|numeric',
            'relatedTransUid' => 'bail|required|numeric',
            'sessionToken' => 'bail|required|string',
            'transactionUid' => 'bail|required|numeric',
        ];
    }
}
