<?php

namespace App\Http\Requests\DriveMedia\Amatic;

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
            'space'     => 'bail|required|string',
            'login'     => 'bail|required|string',
            'cmd'       => 'bail|required|string',
            'sign'      => 'bail|required|validate_sign'
        ];
    }
}