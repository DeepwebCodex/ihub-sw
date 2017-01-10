<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGaming;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use Illuminate\Support\Facades\Config;

/**
 * Description of Process
 *
 * @author petroff
 */
class ProcessOperations {
    //put your code here
    
    static function commit($data_t) {
        $data = $data_t['s:Body']['GetCommitQueueDataResponse']['GetCommitQueueDataResult']['a:QueueDataResponse'];

        foreach ($data as $key => $value) {
            $user_id = (int) $value['a:LoginName'];
            $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
            $transactionRequest = new TransactionRequest(
            Config::get('integrations.microgaming.service_id'),
            $value['a:TransactionNumber'],
            $user->id,
            $user->getCurrency(),
            MicroGamingHelper::getTransactionDirection(TransactionRequest::TRANS_WIN),
            TransactionHelper::amountCentsToWhole($value['a:ChangeAmount']),
            MicroGamingHelper::getTransactionType(TransactionRequest::TRANS_WIN),
            $value['a:MgsReferenceNumber']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessMicroGaming());
        }
    }
      
}
