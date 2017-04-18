<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\Sign;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EndorphinaValidation
{

    public function checkSign($attribute, $value, $parameters, $validator): bool
    {

        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        $sign = $all['sign'];

        unset($all['sign']);


        if ($value != Sign::generate($all)) {
            throw new ApiHttpException(401, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

    public static function checkCurrency(string $userCurrency, string $packetCurrency): bool
    {

        if ($userCurrency != $packetCurrency) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY));
        }


        return true;
    }

}
