<?php

namespace App\Http\Requests\Simple;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class PayInRequest
 * @package App\Http\Requests\Simple
 */
class PayInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'api_id' => 'required|integer',
            'token' => 'required|string',
            'time' => 'required|numeric',
            'signature' => 'required|string',
            'object_id' => 'required|integer',
            'transaction_id' => 'required|integer',
            'amount' => 'required|integer'
        ];
    }
}
