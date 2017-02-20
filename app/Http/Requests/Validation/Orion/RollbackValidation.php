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
        $this->nameElement = $this->nameCommitRollbackElement;
    }

    public function getElements(array $data): array {
        return $data['s:Body']['GetRollbackQueueDataResponse']['GetRollbackQueueDataResult'][$this->nameElement];
    }

}
