<?php

namespace App\Http\Requests\DriveMedia\Amatic;

use App\Http\Requests\DriveMedia\BaseDriveMediaRequest;

class PlayRequest extends BaseDriveMediaRequest
{
    public function messages()
    {
        return [];
    }

    public function rules()
    {
        return [
            'space'     => 'bail|required|validate_space',
            'login'     => 'bail|required|string',
            'cmd'       => 'bail|required|string',
            'bet'       => 'bail|required',
            'winLose'   => 'bail|required',
            'tradeId'   => 'bail|required|string',
            'betInfo'   => 'bail|required|string',
            'gameId'    => 'bail|required|string',
            'matrix'    => 'bail|required|string',
            'date'      => 'bail|required|integer',
            'sign'      => 'bail|required|validate_sign',
            'userId'    => 'bail|required|integer',
            'partnerId' => 'bail|required|integer',
            'cashdeskId'=> 'bail|required|integer',
            'userIp'    => 'bail|required|string',
        ];
    }
}