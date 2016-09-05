<?php

namespace App\Http\Requests\Simple;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AuthRequest
 * @package App\Http\Requests\Simple
 */
class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'api_id' => 'required|integer',
            'token' => 'required|string',
            'time' => 'required|numeric',
            'signature' => 'required|string'
        ];
    }
}
