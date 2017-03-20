<?php

namespace App\Http\Requests\DriveMediaNovomaticDeluxe;

class WriteBetRequest extends BaseRequest {

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
        $parentRules = parent::rules();
        $childRules = [
            'bet' => 'bail|present|numeric|',
            'winLose' => 'bail|present|numeric|',
            'tradeId' => 'bail|required|string|',
            'betInfo' => 'bail|required|string|',
            'gameId' => 'bail|required|numeric|',
            'matrix' => 'bail|required|',
            'date' => 'bail|required|numeric|',
            'userId'    => 'bail|required|integer',
            'partnerId' => 'bail|required|integer',
            'cashdeskId'=> 'bail|required|integer',
            'userIp'    => 'bail|required|string',
        ];
        return array_merge($childRules, $parentRules);
    }

}
