<?php

namespace App\Http\Requests\DriveMedia\DriveCasino;

use App\Http\Requests\DriveMedia\BaseDriveMediaRequest;

class BalanceRequest extends BaseDriveMediaRequest
{

    public function messages()
    {
        return [];
    }

    public function rules()
    {
        return [
            'cmd'       => 'bail|required|string',
            'space'     => 'bail|required|validate_space',
            'login'     => 'bail|required|string',
            'sign'      => 'bail|required|validate_sign',
            'userId'    => 'bail|required|integer',
            'partnerId' => 'bail|required|integer',
            'cashdeskId'=> 'bail|required|integer',
            'userIp'    => 'bail|required|string',
        ];
    }

}