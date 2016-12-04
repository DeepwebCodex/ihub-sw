<?php

namespace App\Http\Requests\BetGames;

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
    }

    public function failedAuthorization()
    {
    }

    /**
     * @see BetGamesValidation::checkSignature, BetGamesValidation::checkTime, BetGamesValidation::checkToken, BetGamesValidation::checkMethod
     */
    public function rules()
    {
        return [
            'method' => 'bail|required|string|check_method',
            'signature' => 'bail|required|string|check_signature',
            'time' => 'bail|required|integer|check_time',
            'token' => 'bail|required|string|check_token',
            'params' => 'bail|present'
        ];
    }

    public function response(array $errors)
    {
//        var_dump($this->input('method'), $errors); die();
        throw new ApiHttpException('400', null, [
            'code' => key($errors),
            'method' => $this->input('method'),
            'token' => $this->input('token'),
        ]);
    }
}
