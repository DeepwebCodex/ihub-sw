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
        $prefix = $this->getRequestDataPrefix();
        return [
            $prefix . 'amount' => 'bail|required',
            $prefix . 'callerContextId' => 'bail|required|numeric',
            $prefix . 'contextId' => 'bail|required|numeric',
            $prefix . 'originatingPid' => 'bail|required|numeric',
            $prefix . 'partyOriginatingUid' => 'bail|required|numeric',
            $prefix . 'relatedTransUid' => 'bail|required|numeric',
            $prefix . 'sessionToken' => 'bail|required|string',
            $prefix . 'transactionUid' => 'bail|required|numeric',
        ];
    }
}
