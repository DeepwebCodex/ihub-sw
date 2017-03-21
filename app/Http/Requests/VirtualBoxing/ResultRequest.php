<?php

namespace App\Http\Requests\VirtualBoxing;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class ResultRequest extends BaseVirtualBoxingRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'result.event_id' => 'bail|required|numeric',
            'result.tid' => 'bail|required|string',
            'result.round' => 'bail|required|array'
        ];
    }
}
