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
class ManualCompleteValidation extends Validation {

    function __construct() {
        $this->rulesStructures = [
            's:Body' => 'required',
            's:Body.ManuallyCompleteGame' => 'checkEmpty',
        ];
        $this->rulesElements = [
            'a:RowId' => 'required',
            'a:ServerId' => 'required',
            'a:Success' => 'required|boolean'
        ];
        $this->nameElement = 'a:CompleteGameResponse';
    }

    public function getElements(array $data): array {
        return $data['s:Body']['ManuallyCompleteGame'][$this->nameElement];
    }

}
