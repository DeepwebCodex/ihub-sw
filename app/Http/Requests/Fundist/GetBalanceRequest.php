<?php

namespace App\Http\Requests\Fundist;

/**
 * Class BetRequest
 * @package App\Http\Requests\Fundist
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
            'userid' => 'bail|required|string|min:1',
            'currency' => 'bail|required|string|min:2',
        ]);
    }
}
