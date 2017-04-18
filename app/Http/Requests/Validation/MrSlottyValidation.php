<?php

namespace App\Http\Requests\Validation;

use App\Components\Integrations\MrSlotty\CodeMapping;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Api\ApiHttpException;
use App\Components\Integrations\MrSlotty\StatusCode;

class MrSlottyValidation
{
    public function checkHash($attribute, $value, $parameters, $validator):bool
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $all = $request->all();
        $hash = $all['hash'];
        unset($all['hash']);
        ksort($all);

        if($hash != hash_hmac("sha256", http_build_query($all), config('integrations.mrslotty.secret'))) {
            throw new ApiHttpException(401, "", CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

}