<?php

namespace App\Http\Requests\InspiredVirtualGaming;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class ResultRequest extends BaseInspiredRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event' => 'bail|required'
        ];
    }
}
