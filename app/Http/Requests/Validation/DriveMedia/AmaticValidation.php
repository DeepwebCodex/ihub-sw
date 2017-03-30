<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\Amatic\AmaticHelper;
use App\Components\Integrations\DriveMedia\CodeMapping;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;

class AmaticValidation
{
    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $sign = $request->input('sign');
        $all = AmaticHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', AmaticHelper::getKey($all['space']) . http_build_query($all)))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

    public function validateSpace($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        if (!(bool)AmaticHelper::getSpace($request->input('space'))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        };

        return true;
    }

}