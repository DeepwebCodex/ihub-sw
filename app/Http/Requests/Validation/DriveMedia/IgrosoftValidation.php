<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\Igrosoft\IgrosoftHelper;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;

class IgrosoftValidation
{
    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $sign = $request->input('sign');
        $all = IgrosoftHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', IgrosoftHelper::getKey($all['space']) . http_build_query($all)))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

}