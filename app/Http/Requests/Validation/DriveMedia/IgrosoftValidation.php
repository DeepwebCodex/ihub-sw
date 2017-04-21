<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\StatusCode;
use Illuminate\Support\Facades\Request;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class IgrosoftValidation
{
    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        $sign = $all['sign'];

        unset($all['sign']);
        unset($all['partnerId']);
        unset($all['cashdeskId']);
        unset($all['userId']);
        unset($all['userIp']);

        if($sign != strtoupper(hash('md5', config('integrations.DriveMediaIgrosoft')[$all['space']]['key'] . http_build_query($all))))
        {
            throw new ApiHttpException(500, null, ['code' => StatusCode::INVALID_SIGNATURE]);
        }

        return true;
    }

}