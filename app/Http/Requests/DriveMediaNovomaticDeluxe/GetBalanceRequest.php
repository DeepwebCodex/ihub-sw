<?php

namespace App\Http\Requests\DriveMediaNovomaticDeluxe;

class GetBalanceRequest extends BaseRequest {

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages() {
        return [
                // 'cmd' => 'Cmd must be fill'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return parent::rules();
    }

}
