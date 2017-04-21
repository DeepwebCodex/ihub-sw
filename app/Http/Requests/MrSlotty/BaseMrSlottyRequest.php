<?php

namespace App\Http\Requests\MrSlotty;


use App\Components\Integrations\MrSlotty\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class BaseMrSlottyRequest
 * @package App\Http\Requests\MrSlotty
 */
class BaseMrSlottyRequest extends ApiRequest implements ApiValidationInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return true;
    }

    /**
     * @throws ApiHttpException
     */
    public function failedAuthorization()
    {
        throw new ApiHttpException('401', null, CodeMapping::getByMeaning(CodeMapping::INVALID_AUTH));
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }
}