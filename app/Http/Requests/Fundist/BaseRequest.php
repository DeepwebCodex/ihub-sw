<?php

namespace App\Http\Requests\Fundist;

use App\Components\AppLog;
use App\Components\Integrations\Fundist\ApiMethod;
use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseRequest
 * @package App\Http\Requests\Fundist
 */
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
        if ((new ApiMethod($request->input('type')))->isOffline()) {
            return true;
        }

        try {
            app('GameSession')->start($request->input('i_extparam', ''));
        } catch (SessionDoesNotExist $e) {
            return false;
        }

        $userId = app('GameSession')->get('user_id');

        return ($userId) ? true : false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException(Response::HTTP_OK, null, [
            'code' => StatusCode::TOKEN,
            'method' => $this->input('method'),
            'token' => $this->input('token'),
        ]);
    }

    /**
     * @see FundistValidation::checkHmac, FundistValidation::checkMethod
     */
    public function rules()
    {
        return [
            'type' => 'bail|required|string|check_method',
            'hmac' => 'bail|required|string|check_hmac',
        ];
    }

    /**
     * @param array $errors
     * @throws ApiHttpException
     * @return null
     */
    public function response(array $errors)
    {
        $firstError = array_first($errors);
        if (CodeMapping::isAttribute(key($errors))) {
            $item = [
                'code' => StatusCode::VALIDATION,
                'message' => array_first($firstError)
            ];
            $httpStatus = Response::HTTP_OK;
        } else {
            $item = [
                'code' => StatusCode::UNKNOWN,
            ];
            $httpStatus = Response::HTTP_OK;
        }

        throw new ApiHttpException($httpStatus, null, $item);
    }
}
