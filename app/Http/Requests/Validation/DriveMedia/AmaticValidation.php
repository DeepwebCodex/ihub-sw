<?php

namespace App\Http\Requests\Validation\DriveMedia;

use Illuminate\Support\Facades\Request;
use App\Components\Integrations\DriveMedia\StatusCode;
use App\Exceptions\Api\ApiHttpException;

class AmaticValidation
{
    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        $sign = $all['sign'];
        unset($all['sign']);
        if($sign != strtoupper(hash('md5', config('integrations.DriveMediaAmatic')[$all['space']]['key'] . http_build_query($all))))
        {
            throw new ApiHttpException(500, null, ['code' => StatusCode::INVALID_SIGNATURE]);
        }

        return true;
    }

}