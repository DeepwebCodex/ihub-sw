<?php

namespace App\Http\Requests\Casino;


use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\Simple
 */
class AuthRequest extends BaseCasinoRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'api_id' => 'bail|required|integer',
            'token' => 'bail|required|string|session_token',
            'signature' => 'bail|required|string|check_signature',
            'time' => 'bail|required|numeric|check_time'
        ];
    }
}
