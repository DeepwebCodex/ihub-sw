<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\DriveCasino\DriveCasinoHelper;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;

/**
 * Class DriveCasinoValidation
 * @package App\Http\Requests\Validation\DriveMedia
 */
class DriveCasinoValidation
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validateSign($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $sign = $request->input('sign');
        $all = DriveCasinoHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', DriveCasinoHelper::getKey($all['space']) . http_build_query($all)))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validateSpace($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        if (!(bool)DriveCasinoHelper::getSpace($request->input('space'))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        };

        return true;
    }

}