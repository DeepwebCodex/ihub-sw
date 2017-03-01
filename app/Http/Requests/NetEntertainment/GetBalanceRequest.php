<?php

namespace App\Http\Requests\NetEntertainment;

/**
 * Class BetRequest
 * @package App\Http\Requests\NetEntertainment
 */
class GetBalanceRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'userid' => 'bail|required|integer|min:1',
            'currency' => 'bail|required|string|min:2',
        ]);
    }
}
