<?php

namespace App\Http\Requests\InspiredVirtualGaming;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class VoidRequest extends BaseInspiredRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'Event' => 'bail|required',
        ];
    }
}
