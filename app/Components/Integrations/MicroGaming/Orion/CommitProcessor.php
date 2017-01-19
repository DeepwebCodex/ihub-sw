<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\Transactions\TransactionRequest;
use App\Http\Requests\Validation\Orion\Validation;

/**
 * Description of Process
 *
 * @author petroff
 */
class CommitProcessor extends OperationsProcessor {

    function __construct() {
        parent::__construct('CommitQueue', TransactionRequest::TRANS_WIN);
    }

}
