<?php

namespace App\Http\Requests\EuroGamesTech;


use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Integrations\EuroGamesTech\CodeMapping;
use \App\Components\Integrations\EuroGamesTech\StatusCode;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class BaseEgtRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    protected $codeMapClass = CodeMapping::class;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        $config_user = config('integrations.egt.UserName');
        $config_password = config('integrations.egt.Password');

        if($config_user == $request->input('UserName') && $config_password == $request->input('Password'))
        {
            return true;
        }

        return false;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('403', "Auth failed", array_get(CodeMapping::getByMeaning(CodeMapping::USER_NOT_FOUND), 'code', 0));
    }

    public function rules(){ return []; }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input'),
            [
                'code' => array_get($firstError, 'code', StatusCode::INTERNAL_SERVER_ERROR)
            ]
        );
    }
}
