<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/8/16
 * Time: 12:41 PM
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    private $errorCodes;

    protected $errorCodesConfig = '';
    protected $authAfterValidate = true;
    protected $bailAfterAttributeError = true;

    protected function getFirstError(array $errors){
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

    protected function getErrorCode($attribute){
        $this->getErrorCodes();

        return isset($this->errorCodes[$attribute]) ? $this->errorCodes[$attribute] : 0;
    }

    private function getErrorCodes(){
        if(!empty($this->errorCodesConfig) && !$this->errorCodes){
            $this->errorCodes = config($this->errorCodesConfig);
        }
    }

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validate()
    {
        $instance = $this->getValidatorInstance();

        if($this->authAfterValidate){
            if (!$instance->passes($this->bailAfterAttributeError)) {
                $this->failedValidation($instance);
            } elseif (!$this->passesAuthorization()) {
                $this->failedAuthorization();
            }
        } else {
            if (!$this->passesAuthorization()) {
                $this->failedAuthorization();
            } elseif (!$instance->passes($this->bailAfterAttributeError)) {
                $this->failedValidation($instance);
            }
        }
    }
}