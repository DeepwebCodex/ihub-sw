<?php

namespace App\Http\WirexGaming;

use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\WirexGaming\CodeMapping;
use App\Components\Integrations\WirexGaming\StatusCode;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class BaseRequest
 * @package App\Http\WirexGaming
 */
class BaseRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    protected $codeMapClass = CodeMapping::class;

    /**
     * @return string
     */
    protected function getRequestDataPrefix()
    {
        return 'S:Body.ns2:' . $this->getMetaField('method') . '.request.';
    }

    /**
     * @return bool
     */
    public function isFromTrustedProxy()
    {
        return true;
    }

    /**
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function isSecureRequest()
    {
        if (!$this->isSecure()) {
            $this->addMetaField('methodName', 'https');

            throw new ApiHttpException(
                '400',
                'Only https is allowed',
                CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR)
            );
        }
    }

    /**
     * @param Request $request
     */
    public function authorizeUser(Request $request)
    {
        $dataPrefix = $this->getRequestDataPrefix();
        try {
            app('GameSession')->start($request->input($dataPrefix .'sessionToken', ''));
        } catch (SessionDoesNotExist $e) {
            throw new ApiHttpException(
                400,
                null,
                CodeMapping::getByMeaning(CodeMapping::INVALID_AUTH)
            );
        }

        $userId = app('GameSession')->get('user_id');

        if (!$userId) {
            throw new ApiHttpException(
                400,
                null,
                CodeMapping::getByMeaning(CodeMapping::INVALID_AUTH)
            );
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        if (config('integrations.wirexGaming.use_secure_request', true)) {
            $this->isSecureRequest();
        }

        $dataPrefix = $this->getRequestDataPrefix();

        $configClientPid = config('integrations.wirexGaming.client_pid');
        $configServerPid = config('integrations.wirexGaming.server_pid');

        if ($configClientPid == $request->input($dataPrefix . 'clientPid')
            && $configServerPid == $request->input($dataPrefix . 'serverPid')
        ) {
            $this->authorizeUser($request);

            return true;
        }
        return false;
    }

    /**
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function failedAuthorization()
    {
        throw new ApiHttpException(
            '401',
            null,
            CodeMapping::getByMeaning(CodeMapping::INVALID_AUTH)
        );
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @param array $errors
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException(
            '400',
            array_get($firstError, 'message', 'Invalid input'),
            [
                'code' => StatusCode::SYSTEM_ERROR_CODE
            ]
        );
    }
}
