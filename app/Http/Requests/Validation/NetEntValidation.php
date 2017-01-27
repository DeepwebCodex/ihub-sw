<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\NetEnt\ApiMethod;
use App\Components\Integrations\NetEnt\Hmac;
use App\Components\Integrations\NetEnt\StatusCode;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class NetEntValidation
{
    public function checkHmac($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        unset($all['hmac']);
        $hmac = new Hmac($all, $value);
        if (!$hmac->isCorrect()) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::HMAC,
            ]);
        }

        return true;
    }

    public function checkMethod($attribute, $value, $parameters, $validator):bool
    {
        $apiMethod = new ApiMethod($value);
        if (!$apiMethod->get()) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::METHOD,
            ]);
        }

        return true;
    }
}