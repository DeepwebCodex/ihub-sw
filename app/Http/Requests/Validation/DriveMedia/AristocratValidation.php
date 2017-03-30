<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\Aristocrat\AristocratHelper;
use App\Components\Integrations\DriveMedia\CodeMapping;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;

class AristocratValidation
{

    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $sign = $request->input('sign');
        $all = AristocratHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', AristocratHelper::getKey($all['space']) . http_build_query($all)))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

}