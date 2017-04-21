<?php

namespace App\Http\Requests\VirtualBoxing;

use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class BaseVirtualBoxingRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return true;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException(404, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::METHOD_NOT_FOUND));
    }

    public function response(array $errors)
    {
        throw new ApiHttpException(200, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::MISS_ELEMENT));
    }

    function rules()
    {
        return [];
    }
}
