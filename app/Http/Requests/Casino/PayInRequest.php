<?php

namespace App\Http\Requests\Casino;

/**
 * Class PayInRequest
 * @package App\Http\Requests\Simple
 */
class PayInRequest extends BaseCasinoRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'token' => 'Invalid token',
            'signature'  => 'Invalid signature',
            'time'  => 'Time expired',
            'check_amount' => 'Amount mush be greater that 0'
        ];
    }

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
            'amount' => 'bail|required|integer|check_amount'
        ];
    }
}
