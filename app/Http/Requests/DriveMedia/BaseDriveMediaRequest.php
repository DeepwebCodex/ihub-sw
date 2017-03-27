<?php

namespace App\Http\Requests\DriveMedia;

use App\Http\Requests\ApiRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiValidationInterface;
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

}