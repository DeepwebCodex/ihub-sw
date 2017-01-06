<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Requests\Validation\Orion;

/**
 * Description of CommitValidation
 *
 * @author petroff
 */
class CommitValidation extends Validation {

    function __construct() {
        $this->rules = [];
        $this->rulesStructures = [
            's:Body' => 'required',
            's:Body.GetCommitQueueDataResponse' => 'required',
            's:Body.GetCommitQueueDataResponse.GetCommitQueueDataResult' => 'required',
            's:Body.GetCommitQueueDataResponse.GetCommitQueueDataResult.a:QueueDataResponse' => 'required',
        ];
    }

}
