<?php

namespace App\Http\Requests\MicroGaming;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class LogInRequest extends BaseMicroGamingRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            /*'DefenceCode.check_defence_code' => 'Invalid defence code',
            'DefenceCode.check_expiration_time'  => 'Expired defence code',*/
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
            'methodcall.call.seq' => 'bail|required|string',
            'methodcall.call.token' => 'bail|required|string',
        ];
    }
}
