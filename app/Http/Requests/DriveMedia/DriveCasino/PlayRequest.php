<?php

namespace App\Http\Requests\DriveMedia\DriveCasino;

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
            'cmd'       => 'bail|required|string',
            'space'     => 'bail|required|string',
            'login'     => 'bail|required|string',
            'bet'       => 'bail|required',
            'winLose'   => 'bail|required',
            'tradeId'   => 'bail|required|string',
            'betInfo'   => 'bail|required|string',
            'gameId'    => 'bail|required|string',
            'matrix'    => 'bail|required|integer',
            'WinLines'  => 'bail|required|integer',
            'date'      => 'bail|required|integer',
            'sign'      => 'bail|required|validate_sign',
            'userId'    => 'bail|required|integer',
            'partnerId' => 'bail|required|integer',
            'cashdeskId'=> 'bail|required|integer',
            'userIp'    => 'bail|required|string',
        ];
    }

}