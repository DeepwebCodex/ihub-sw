<?php

namespace App\Http\Requests\EuroGamesTech;

use App\Components\Integrations\EuroGamesTech\StatusCode;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class AuthRequest extends BaseEgtRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'DefenceCode.validate_defence_code' => 'Invalid defence code',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        if(parent::authorize($request)){
            try{
                app('GameSession')->start($request->input('DefenceCode', ''));
            } catch (SessionDoesNotExist $e) {
                throw new ApiHttpException(400, "Defence code expired", ['code' => StatusCode::EXPIRED]);
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'UserName' => 'bail|required|string',
            'Password' => 'bail|required|string',
            'PlayerId' => 'bail|required|integer',
            'DefenceCode' => 'bail|required|string|validate_defence_code',
            'PortalCode' => 'bail|required|string',
            'SessionId' => 'bail|required|string',
        ];
    }
}
