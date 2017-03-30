<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\DriveCasino\DriveCasinoHelper;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;

class DriveCasinoValidation
{
    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $sign = $request->input('sign');
        $all = DriveCasinoHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', DriveCasinoHelper::getKey($all['space']) . http_build_query($all))))
        {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

}