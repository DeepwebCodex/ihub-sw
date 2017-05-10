<?php

namespace App\Http\WirexGaming;

use Illuminate\Http\Request;

/**
 * Class RollbackWithdrawRequest
 * @package App\Http\WirexGaming
 */
class RollbackWithdrawRequest extends BaseRequest
{
    /**
     * @return string
     */
    protected function getRequestDataPrefix()
    {
        return 'S:Body.ns2:' . $this->getMetaField('method') . '.transactionRequest.';
    }

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
            $prefix . 'transactionUid' => 'bail|required|numeric',
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
