<?php

namespace App\Http\WirexGaming;

/**
 * Class AddWithdrawRequest
 * @package App\Http\WirexGaming
 */
class AddWithdrawRequest extends BaseRequest
{
    /**
     * @return string
     */
    protected function getRequestDataPrefix()
    {
        return 'S:Body.ns2:' . $this->getMetaField('method') . '.accountEntryPlatformRequest.';
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
            $prefix . 'accountEntryDetailed.accountEntry.amount' => 'bail|required',
            $prefix . 'partyOriginatingUid' => 'bail|required|numeric',
            $prefix . 'sessionToken' => 'bail|required|string',
            $prefix . 'transactionUid' => 'bail|required|numeric',
        ];
    }
}
