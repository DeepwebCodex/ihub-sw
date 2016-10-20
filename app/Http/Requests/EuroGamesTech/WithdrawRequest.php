<?php

namespace App\Http\Requests\EuroGamesTech;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class WithdrawRequest extends BaseEgtRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'Reason.validate_withdraw' => 'Invalid withdraw reason',
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
            'UserName'      => 'bail|required|string',
            'Password'      => 'bail|required|string',
            'PlayerId'      => 'bail|required|integer',
            'TransferId'    => 'bail|required|string',
            'GameId'        => 'bail|required|integer',
            'GameNumber'    => 'bail|required|integer',
            'Amount'        => 'bail|required|numeric|min:0',
            'Currency'      => 'bail|required|string',
            'Reason'        => 'bail|required|string|validate_withdraw',
            'PortalCode' => 'bail|required|string',
            'SessionId' => 'bail|required|string'
        ];
    }
}
