<?php

namespace App\Http\Requests\VirtualBoxing;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class MatchBetRequest extends BaseVirtualBoxingRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'match.scheduleId' => 'bail|required|numeric',
            'match.competition' => 'bail|required|string',
            'match.bet' => 'bail|required|array',
            'match.away' => 'bail|required|string',
            'match.home' => 'bail|required|string',
            'match.location' => 'bail|required|string',
            'match.date' => 'bail|required|date_format:Y-m-d',
            'match.time' => 'bail|required|date_format:H:i:s',
            'match.name' => 'bail|required|string'
        ];
    }
}
