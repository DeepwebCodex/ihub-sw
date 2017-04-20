<?php

namespace App\Http\Requests\Endorphina;

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
            'currency' => 'bail|required|string',
            'game' => 'bail|required|string',
            'player' => 'bail|required|string',
            'amount' => 'bail|required|numeric|min:1',
            'date' => 'bail|required|numeric',
            'gameId' => 'bail|required|numeric|min:1',
            'id' => 'bail|required|numeric',
        ]);
    }

}
