<?php

namespace App\Http\Requests\NetEnt;

use App\Components\Integrations\NetEnt\CodeMapping;
use App\Components\Integrations\NetEnt\StatusCode;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class BaseRequest
 * @package App\Http\Requests\NetEnt
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
        try {
            app('GameSession')->start($request->input('token', ''));
        } catch (SessionDoesNotExist $e) {
            return false;
        }

        $userId = app('GameSession')->get('user_id');

        if ($userId) {
//            $this->addMetaField('token', $request->input('token'));
            return true;
        }

        return false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException(403, null, [
            'code' => StatusCode::TOKEN,
            'method' => $this->input('method'),
            'token' => $this->input('token'),
        ]);
    }

    /**
     * @see NetEntValidation::checkHmac, NetEntValidation::checkMethod
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
        if (CodeMapping::isTransactionAttribute(key($errors))) {
            $item = [
                'code' => StatusCode::VALIDATION,
                'message' => 'Transaction parameter mismatch'
            ];
            $httpStatus = 400;
        } elseif (CodeMapping::isAttribute(key($errors))) {
            $item = [
                'code' => StatusCode::VALIDATION,
                'message' => array_first($firstError)
            ];
            $httpStatus = 400;
        } else {
            $item = [
                'code' => StatusCode::UNKNOWN,
                'message' => ''
            ];
            $httpStatus = 500;
        }

        throw new ApiHttpException($httpStatus, null, $item);
    }
}
