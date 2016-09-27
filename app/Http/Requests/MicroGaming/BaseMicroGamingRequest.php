<?php

namespace App\Http\Requests\MicroGaming;

use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class BaseMicroGamingRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    protected $codeMapClass = CodeMapping::class;

    public function isFromTrustedProxy()
    {
        return true;
    }

    public function isSecureRequest()
    {
        if (!$this->isSecure()) {
            $this->addMetaField('methodName', 'https');

            throw new ApiHttpException('400', "Only https is allowed", [array_get('code', CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR))]);
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
        //$this->isSecureRequest();

        $config_user = config('integrations.microGaming.login_server');
        $config_password = config('integrations.microGaming.password_server');

        if($config_user == $request->input('methodcall.auth.login') && $config_password == $request->input('methodcall.auth.password'))
        {
            return true;
        }

        return false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('401', null, CodeMapping::getByMeaning(CodeMapping::INVALID_AUTH));
    }

    public function rules(){ return []; }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input'),
            [
                'code' => array_get($firstError, 'code', 6000)
            ]
        );
    }
}
