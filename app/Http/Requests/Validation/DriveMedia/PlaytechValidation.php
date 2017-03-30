<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\Playtech\PlaytechHelper;
use App\Components\Integrations\DriveMedia\StatusCode;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;

/**
 * Class PlaytechValidation
 * @package App\Http\Requests\Validation\DriveMedia
 */
class PlaytechValidation
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
        $all = PlaytechHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', PlaytechHelper::getKey($all['space']) . http_build_query($all))))
        {
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

        if (!(bool)PlaytechHelper::getSpace($request->input('space'))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        };

        return true;
    }

}