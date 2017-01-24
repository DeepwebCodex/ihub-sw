<?php

namespace App\Http\Requests\EuroGamesTech;

/**
 * Class PlayerBalanceRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class PlayerBalanceRequest extends BaseEgtRequest
{
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
            'PortalCode' => 'bail|required|string',
            'SessionId' => 'bail|required|string',
            'Currency'  => 'bail|required|string',
            'GameId'    => 'bail|required|integer',
            'PartnerId' => 'bail|integer|nullable',
            'CashdeskId' => 'bail|integer|nullable',
        ];
    }
}
