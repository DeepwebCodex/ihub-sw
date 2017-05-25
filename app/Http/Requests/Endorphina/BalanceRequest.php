<?php

use App\Http\Requests\Endorphina\BaseRequest;

namespace App\Http\Requests\Endorphina;


class BalanceRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'currency' => 'bail|sometimes|string',
            'game'=> 'bail|sometimes|string',
            'player' => 'bail|sometimes|string',
        ]);
    }

}
