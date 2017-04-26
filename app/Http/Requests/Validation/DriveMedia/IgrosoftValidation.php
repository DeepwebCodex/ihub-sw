<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMedia\StatusCode;
use Illuminate\Support\Facades\Request;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\Igrosoft\IgrosoftHelper;

/**
 * Class IgrosoftValidation
 * @package App\Http\Requests\Validation\DriveMedia
 */
class IgrosoftValidation
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
        $all = IgrosoftHelper::clearRequest($request->request->all());

        if($sign != strtoupper(hash('md5', IgrosoftHelper::getKey($all['space']) . http_build_query($all)))) {
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

        if (!(bool)IgrosoftHelper::getSpace($request->input('space'))) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        };

        return true;
    }

}