<?php

namespace App\Http\Requests\Casino;

/**
 * Class PayOutRequest
 * @package App\Http\Requests\Simple
 */
class PayOutRequest extends BaseCasinoRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'api_id' => 'bail|required|integer',
            'token' => 'bail|required|string|session_token',
            'signature' => 'bail|required|string|check_signature',
            'time' => 'bail|required|numeric|check_time',
            'object_id' => 'bail|required|integer',
            'transaction_id' => 'bail|required|integer',
            'amount' => 'bail|required|integer|check_amount',
            'user_id' => 'bail|required|integer',
            'type_operation' => 'bail|required|string'
        ];
    }
}
