<?php

namespace App\Http\Requests\Casino;


use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\Simple
 */
class BaseCasinoRequest extends ApiRequest implements ApiValidationInterface
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
        throw new ApiHttpException('403', null, CodeMapping::getByMeaning(CodeMapping::USER_NOT_FOUND));
    }

    public function rules(){ return []; }

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
