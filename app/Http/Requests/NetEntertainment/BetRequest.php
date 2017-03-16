<?php

namespace App\Http\Requests\NetEntertainment;

/**
 * Class BetRequest
 * @package App\Http\Requests\NetEntertainment
 */
class BetRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'tid' => 'bail|required|min:1',
            'userid' => 'bail|required|string|min:1',
            'currency' => 'bail|required|string|min:2',
            'amount' => 'bail|required|numeric|min:0.01',
        ]);
    }
}
