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
        ];
    }

    public function validateBaseStructure(array $data): bool {
        parent::validateBaseStructure($data);
        if (isset($data['s:Body']['GetCommitQueueDataResponse']['GetCommitQueueDataResult']['a:QueueDataResponse'])) {
            $dataT = self::getData($data);
            foreach ($dataT as $key => $value) {
                $this->validate($value, $this->rulesStructuresCoommitRollback);
            }
        }
        return true;
    }

    static function getData(array $data): array {
        if (isset($data['s:Body']['GetCommitQueueDataResponse']['GetCommitQueueDataResult']['a:QueueDataResponse'][0])) {
            $dataT = $data['s:Body']['GetCommitQueueDataResponse']['GetCommitQueueDataResult']['a:QueueDataResponse'];
        } else {
            $dataT[] = $data['s:Body']['GetCommitQueueDataResponse']['GetCommitQueueDataResult']['a:QueueDataResponse'];
        }
        return $dataT;
    }

}
