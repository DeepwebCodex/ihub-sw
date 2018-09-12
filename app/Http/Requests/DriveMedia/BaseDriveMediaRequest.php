<?php

namespace App\Http\Requests\DriveMedia;

use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

class BaseDriveMediaRequest extends ApiRequest implements ApiValidationInterface
{
    public function authorize(Request $request)
    {
        return true;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('401', 'Invalid auth');
    }

    public function rules() {
        return [];
    }

    function response(array $errors)
    {
        // TODO: Implement response() method.
    }
}