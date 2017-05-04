<?php

namespace App\Http\Requests\EuroGamesTech;

use App\Components\Integrations\EuroGamesTech\CodeMapping;
use \App\Components\Integrations\EuroGamesTech\StatusCode;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Requests\ApiRequest;
use iHubGrid\ErrorHandler\Http\Requests\ApiValidationInterface;
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
        return
            config('integrations.egt.UserName') == $request->input('UserName') &&
            config('integrations.egt.Password') == $request->input('Password');
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('403', "Auth failed", CodeMapping::getByMeaning(CodeMapping::USER_NOT_FOUND));
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
