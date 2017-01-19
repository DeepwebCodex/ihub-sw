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
class RollbackValidation extends Validation {

    function __construct() {
        $this->rules = [];
        $this->rulesStructures = [
            's:Body' => 'required',
            's:Body.GetRollbackQueueDataResponse' => 'required',
            's:Body.GetRollbackQueueDataResponse.GetRollbackQueueDataResult' => 'checkEmpty',
        ];
        $this->rulesElements = $this->rulesRollbackCommit;
    }

    public function getData(array $data): array {
        $this->elements = $data['s:Body']['GetRollbackQueueDataResponse']['GetRollbackQueueDataResult']['a:QueueDataResponse'];
        if (isset($this->elements[0])) {
            $dataT = $this->elements;
        } else {
            $dataT[] = $this->elements;
        }
        return $dataT;
    }

}
