<?php

namespace App\Http\Requests\DriveMediaNovomatic;

use App\Components\Integrations\DriveMediaNovomatic\CodeMapping;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class BaseNovomaticRequest extends ApiRequest implements ApiValidationInterface
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
     * @throws \App\Exceptions\Api\ApiHttpException
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
