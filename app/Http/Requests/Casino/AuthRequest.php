<?php

namespace App\Http\Requests\Casino;


/**
 * Class AuthRequest
 * @package App\Http\Requests\Simple
 */
class AuthRequest extends BaseCasinoRequest
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
            'time'  => 'Time expired'
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
            'time' => 'bail|required|numeric|check_time'
        ];
    }
}
