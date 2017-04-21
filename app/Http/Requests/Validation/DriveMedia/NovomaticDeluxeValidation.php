<?php

namespace App\Http\Requests\Validation\DriveMedia;

use App\Components\Integrations\DriveMediaNovomaticDeluxe\CodeMapping;
use App\Components\Integrations\DriveMediaNovomaticDeluxe\Sign;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use function array_get;

class NovomaticDeluxeValidation {

    use MetaDataTrait;

    public function checkSign($attribute, $value, $parameters, $validator): bool {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $all = $this->pullMetaField('imprint');
        unset($all['userId']);
        unset($all['userIp']);
        unset($all['partnerId']);
        unset($all['cashdeskId']);

        if ($value != Sign::generate($all)) {
            throw new ApiHttpException(400, null, CodeMapping::getByMeaning(CodeMapping::INVALID_SIGNATURE));
        }

        return true;
    }

    public static function checkCurrency(string $userCurrency, string $space): bool {
        if (App::environment('production')) {
            $spaces = Config::get("integrations.DriveMediaNovomaticDeluxe.spaces");
            $currency = array_get($spaces, $space . ".currency");

            if ($userCurrency != $currency) {
                throw new ApiHttpException(400, CodeMapping::INVALID_CURRENCY);
            }
        }

        return true;
    }

}
