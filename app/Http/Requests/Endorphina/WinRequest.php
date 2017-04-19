<?php

namespace App\Http\Requests\Endorphina;

class WinRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'currency' => 'bail|required|string',
            'game' => 'bail|required|string',
            'player' => 'bail|required|string',
            'amount' => 'bail|required|numeric',
            'date' => 'bail|required|numeric',
            'gameId' => 'bail|required|numeric|min:1',
            'id' => 'bail|sometimes|required|numeric',
        ];
    }

}
