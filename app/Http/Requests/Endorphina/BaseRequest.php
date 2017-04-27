<?php

namespace App\Http\Requests\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use Illuminate\Http\Request;


class BaseRequest extends ApiRequest implements ApiValidationInterface
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

        try {
            app('GameSession')->start(strtolower($request->input('token', '')));
        } catch (SessionDoesNotExist $e) {
            return false;
        }

        $userId = app('GameSession')->get('user_id');

        return ($userId) ? true : false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException(404, null, CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
    }

    public function rules()
    {
        return [
            'token' => 'bail|required|string',
            'sign' => 'bail|required|string|check_sign',
        ];
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('500', array_get($firstError, 'message', 'Invalid input'), [
    'code' => array_get($firstError, 'code', StatusCode::SERVER_ERROR)
        ]
        );
    }

}
