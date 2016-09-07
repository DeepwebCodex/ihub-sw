<?php

namespace App\Http\Requests\Simple;

use App\Exceptions\Api\ApiHttpException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\Simple
 */
class AuthRequest extends FormRequest
{
    private $errorCodes = [
        'signature' => 11,
        'time'      => 10,
        'token'     => 6
    ];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        //exit(dump($request->all()));
        return true;
    }

    protected function failedAuthorization()
    {
        throw new ApiHttpException('403', "User not found", ['code' => 4]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'api_id' => 'bail|required|integer',
            'token' => 'bail|required|string|session_token',
            'signature' => 'bail|required|string|check_signature',
            'time' => 'bail|required|numeric|check_time'
        ];
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input'),
            [
                'code' => array_get($firstError, 'code', 0),
                'validation' => $errors
            ]
        );
    }

    private function getFirstError(array $errors){
        if($errors){
            foreach ($errors as $attribute => $error){
                if(is_array($error)){
                    return [
                        'message' => reset($error),
                        'code'    => $this->getErrorCode($attribute)
                    ];
                } else {
                    return [
                        'message' => $error,
                        'code'    => $this->getErrorCode($attribute)
                    ];
                }
            }
        }

        return [];
    }

    private function getErrorCode($attribute){
        return isset($this->errorCodes[$attribute]) ? $this->errorCodes[$attribute] : 0;
    }
}
