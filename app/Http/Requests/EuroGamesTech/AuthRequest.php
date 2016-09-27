<?php

namespace App\Http\Requests\EuroGamesTech;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class AuthRequest extends BaseEgtRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'DefenceCode.check_defence_code' => 'Invalid defence code',
            'DefenceCode.check_expiration_time'  => 'Expired defence code',
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
            'UserName' => 'bail|required|string',
            'Password' => 'bail|required|string',
            'PlayerId' => 'bail|required|integer',
            'DefenceCode' => 'bail|required|string|check_defence_code|check_expiration_time',
            'PortalCode' => 'bail|required|string',
            'SessionId' => 'bail|required|string'
        ];
    }
}
