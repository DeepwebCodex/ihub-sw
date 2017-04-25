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

/**
 * Class NovomaticDeluxeValidation
 * @package App\Http\Requests\Validation\DriveMedia
 */
class NovomaticDeluxeValidation {

    use MetaDataTrait;

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
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

    /**
     * @param string $userCurrency
     * @param string $space
     */
    public static function checkCurrency(string $userCurrency, string $space) {
        $userSpace = Config::get("integrations.DriveMediaNovomaticDeluxe.spaces.{$userCurrency}.id");

        if ($userSpace !== $space) {
            throw new ApiHttpException(400, CodeMapping::INVALID_CURRENCY);
        }
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     */
    public function validateSpace($attribute, $value, $parameters, $validator) {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $all = $request->all();

        if(!(bool)$this->getSpace($all['space'])) {
            throw new ApiHttpException(500, CodeMapping::SERVER_ERROR);
        }

        return true;
    }

    /**
     * @param string $space
     * @return bool
     */
    protected function getSpace(string $space):bool
    {
        $spaces = Config::get("integrations.DriveMediaNovomaticDeluxe.spaces");

        foreach ($spaces as $v) {
            if($v['id'] === $space) {
                return true;
            }
        }

        return false;
    }

}
