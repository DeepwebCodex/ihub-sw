<?php

namespace App\Http\Requests\MrSlotty;

/**
 * Class BalanceRequest
 * @package App\Http\Requests\MrSlotty
 */
class BalanceRequest extends BaseMrSlottyRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'action'    => 'bail|required|string',
            'player_id' => 'bail|required|string',
            'currency'  => 'bail|string',
            'hash'      => 'bail|required|check_hash',
            'extra'     => 'bail|string'
        ];
    }
}