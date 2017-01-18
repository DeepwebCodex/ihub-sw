<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Requests\Validation\Orion;

use Exception;
use Validator;

/**
 * Description of Validation
 *
 * @author petroff
 */
class Validation {

    protected $rules = [];
    protected $rulesStructures = [];
    protected $rulesStructuresCoommitRollback = [
        'a:LoginName' => 'required',
        'a:UserId' => 'required',
        'a:ChangeAmount' => 'required',
        'a:TransactionCurrency' => 'required',
        'a:Status' => 'required',
        'a:RowId' => 'required',
        'a:TransactionNumber' => 'required',
        'a:GameName' => 'required',
        'a:DateCreated' => 'required',
        'a:MgsReferenceNumber' => 'required',
        'a:ServerId' => 'required',
        'a:MgsPayoutReferenceNumber' => 'required',
        'a:PayoutAmount' => 'required',
        'a:ProgressiveWin' => 'required',
//        'a:ProgressiveWinDesc' => 'required',
//        'a:FreeGameOfferName' => 'required',
        'a:TournamentId' => 'required',
//        'a:Description' => 'required',
//        'a:ExtInfo' => 'required',
        'a:RowIdLong' => 'required',
    ];
    protected $errors;

    protected function validate(array $data, array $rules): bool {
        $v = Validator::make($data, $rules);

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->errors();
            throw new Exception("Error validation" . print_r($this->errors, true));
        }

        // validation pass
        return true;
    }

    public function validateBaseStructure(array $data): bool {
        return $this->validate($data, $this->rulesStructures);
    }

    public function errors(): array {
        return $this->errors;
    }

}
