<?php

namespace App\Components\Transactions\Strategies\MicroGaming;

use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class ProcessMicroGamingOrion extends ProcessMicroGaming implements TransactionProcessorInterface
{


    protected function getBetRecords()
    {
        $betTransaction = parent::getBetRecords();
        if($betTransaction){
               $this->request->partner_id = $betTransaction->partner_id;
               $this->request->cashdesk_id = $betTransaction->cashdesk;
        }
        return $betTransaction;
    }
    
  
}