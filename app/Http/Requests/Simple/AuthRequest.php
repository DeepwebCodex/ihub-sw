<?php

namespace App\Http\Requests\Simple;


use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\Simple
 */
class AuthRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    protected $errorCodesConfig = 'integrations.casino.error_codes';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        $userId = RemoteSession::start($request->input('token'))->get('user_id');

        if($userId){
            $this->addMetaField('user_id', $userId);
            $this->addMetaField('token', $request->input('token'));
            return true;
        }

        return false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('403', "User not found", ['code' => $this->getErrorCode('user_not_found')]);
    }

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
            //'signature' => 'bail|required|string|check_signature',
            //'time' => 'bail|required|numeric|check_time'
        ];
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input'),
            [
                'code' => array_get($firstError, 'code', 0)
            ]
        );
    }
}
