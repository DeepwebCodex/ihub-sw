<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMediaNovomatic\SignatureMaker;
use App\Components\Integrations\DriveMediaNovomatic\StatusCode;
use App\Exceptions\Api\ApiHttpException;
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function validateSign($attribute, $value, $parameters, $validator): bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        $sign = $all['sign'];
        unset($all['sign']);
        $space = $all['space'];
        $signatureMaker = new SignatureMaker();
        $signature = $signatureMaker->make($space, $all);
        if ($sign !== $signature) {
            throw new ApiHttpException(200, null, ['code' => StatusCode::INVALID_SIGNATURE]);
        }
        return true;
    }
}
