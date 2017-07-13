<?php
namespace App\Http\Requests\Validation;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\Sign;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;

class EndorphinaValidation
{

    public function checkSign($attribute, $sign, $parameters, $validator): bool
    {

        if (!($request = Request::getFacadeRoot())) {
            return false;
        }
        $all = $request->all();
        if ($request->has('game')) {
            $all['game'] = urldecode($request->input('game'));
        }
        unset($all['sign']);


        if (strtoupper($sign) != Sign::generate($all)) {
            throw new ApiHttpException(401, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

    public static function checkCurrency(string $userCurrency, string $packetCurrency)
    {
        if ($userCurrency != $packetCurrency) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY));
        }
    }
}
