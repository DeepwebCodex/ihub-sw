<?php

namespace App\Http\Requests\EuroGamesTech;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class DepositRequest extends BaseEgtRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'Reason.validate_deposit' => 'Invalid deposit reason',
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
            'TransferId' => 'bail|required|string',
            'GameId' => 'bail|required|integer',
            'GameNumber' => 'bail|required|integer',
            'SessionId' => 'bail|required|string',
            'Amount' => 'bail|required|numeric|min:0',
            'Currency' => 'bail|required|string',
            'Reason' => 'bail|required|string|validate_deposit',
            'PortalCode' => 'bail|required|string',
            'PartnerId' => 'bail|integer|nullable',
            'CashdeskId' => 'bail|integer|nullable',
            'UserIp'    => 'bail|string|nullable'
        ];
    }
}
