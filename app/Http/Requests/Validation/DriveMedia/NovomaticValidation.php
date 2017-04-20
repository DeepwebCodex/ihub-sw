<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMediaNovomatic\SignatureMaker;
use App\Components\Integrations\DriveMediaNovomatic\StatusCode;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

/**
 * Class NovomaticValidation
 * @package App\Http\Requests\Validation
 */
class NovomaticValidation
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     * @throws ApiHttpException
     */
    public function validateSign($attribute, $value, $parameters, $validator): bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        $sign = $all['sign'];

        unset($all['sign']);
        unset($all['partnerId']);
        unset($all['userIp']);
        unset($all['cashdeskId']);
        unset($all['userId']);

        $space = $all['space'];

        $signatureMaker = new SignatureMaker();
        $signature = $signatureMaker->make($space, $all);
        if ($sign !== $signature) {
            throw new ApiHttpException(200, null, ['code' => StatusCode::INVALID_SIGNATURE]);
        }
        return true;
    }

    public function validateSpace($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        if (!(bool)config("integrations.DriveMediaNovomatic.spaces.{$request->input('space')}", false)) {
            throw new ApiHttpException(500, null, ['code' => StatusCode::SPACE_NOT_FOUND]);
        };

        return true;
    }
}
