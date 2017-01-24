<?php

namespace App\Http\Requests\VirtualBoxing;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class MatchProgressRequest extends BaseVirtualBoxingRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event_id' => 'bail|required|numeric',
            'mnem' => 'bail|required|in:MB',
            'name' => 'bail|required|in:match_progress',
            'xu:ups-at.xu:at.0.#text' => 'bail|required'
        ];
    }
}
