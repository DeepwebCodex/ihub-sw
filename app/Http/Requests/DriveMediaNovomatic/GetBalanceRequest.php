<?php

namespace App\Http\Requests\DriveMediaNovomatic;

/**
 * Class BalanceRequest
 * @package App\Http\Requests\Novomatic
 */
class GetBalanceRequest extends BaseNovomaticRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'sign.validate_sign' => 'Invalid sign'
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
            'space' => 'bail|required|numeric',
            'login' => 'bail|required|string',
            'sign' => 'bail|required|string|validate_sign',
            'userId'    => 'bail|required|integer',
            'partnerId' => 'bail|required|integer',
            'cashdeskId'=> 'bail|required|integer',
            'userIp'    => 'bail|required|string',
        ];
    }
}
