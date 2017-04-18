<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\NetEntertainment\ApiMethod;
use App\Components\Integrations\NetEntertainment\Hmac;
use App\Components\Integrations\NetEntertainment\StatusCode;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class NetEntertainmentValidation
{
    public function checkHmac($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        unset($all['hmac']);
        unset($all['userId']);
        if (!(new Hmac($all, $value))->isCorrect()) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::HMAC,
            ]);
        }

        return true;
    }

    public function checkMethod($attribute, $value, $parameters, $validator):bool
    {
        $apiMethod = new ApiMethod($value);
        if (!$apiMethod->get()) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::METHOD,
            ]);
        }

        return true;
    }
}