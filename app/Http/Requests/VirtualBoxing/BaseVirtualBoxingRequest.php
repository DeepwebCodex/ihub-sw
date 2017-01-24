<?php

namespace App\Http\Requests\VirtualBoxing;

use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
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
        throw new ApiHttpException(503, "Service not available");
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input')
        );
    }

    function rules()
    {
        return [];
    }
}
