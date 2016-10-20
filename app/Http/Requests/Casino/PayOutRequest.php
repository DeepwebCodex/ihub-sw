<?php

namespace App\Http\Requests\Casino;

use Illuminate\Http\Request;

/**
 * Class PayOutRequest
 * @package App\Http\Requests\Simple
 */
class PayOutRequest extends BaseCasinoRequest
{

    /**
     * User is not required to be logged in for this request
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        $this->addMetaField('user_id', $request->input('user_id'));
        $this->addMetaField('token', $request->input('token'));

        return true;
    }

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
            'time' => 'bail|required|numeric|check_time',
            'object_id' => 'bail|required|integer',
            'transaction_id' => 'bail|required|integer',
            'amount' => 'bail|required|integer|check_amount',
            'user_id' => 'bail|required|integer',
            'type_operation' => 'bail|required|string'
        ];
    }
}
