<?php

namespace App\Http\Requests\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;
use function app;
use function array_get;

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
            app('GameSession')->start($request->input('token', ''));
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
            'token' => 'bail|required|string|',
            'sign' => 'bail|required|string|check_sign|',
        ];
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400', array_get($firstError, 'message', 'Invalid input'), [
    'code' => array_get($firstError, 'code', StatusCode::SERVER_ERROR)
        ]
        );
    }

}
