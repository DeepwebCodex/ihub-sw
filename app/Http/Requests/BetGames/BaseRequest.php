<?php

namespace App\Http\Requests\BetGames;

use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class BaseRequest
 * @package App\Http\Requests\BetGames
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
        try{
            app('GameSession')->start($request->input('token', ''));
        } catch (SessionDoesNotExist $e) {
            return false;
        }

        $userId = app('GameSession')->get('user_id');

        if($userId){
            $this->addMetaField('user_id', $userId);
            $this->addMetaField('token', $request->input('token'));
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
     * @see BetGamesValidation::checkSignature, BetGamesValidation::checkTime, BetGamesValidation::checkMethod
     */
    public function rules()
    {
        return [
            'method' => 'bail|required|string|check_method',
            'signature' => 'bail|required|string|check_signature',
            'time' => 'bail|required|integer|check_time',
            'token' => 'bail|required|string',
            'params' => 'bail|present'
        ];
    }

    public function response(array $errors)
    {
        throw new ApiHttpException('400', null, array_merge(CodeMapping::getByErrorCode(StatusCode::SIGNATURE), [
            'method' => $this->input('method'),
            'token' => $this->input('token'),
        ]));
    }
}
