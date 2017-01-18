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
use App\Components\Transactions\TransactionResponse;
use App\Components\Users\IntegrationUser;
use App\Facades\AppLog;
use App\Http\Requests\Validation\Orion\CommitValidation;
use Exception;
use Illuminate\Support\Facades\Config;

/**
 * Description of Process
 *
 * @author petroff
 */
class OperationsProcessor {

    //RollbackQueue

    static function commit(array $data_t): array {

        $data = CommitValidation::getData($data_t);
        $dataRes = array();
        foreach ($data as $key => $value) {
            $user_id = (int) $value['a:LoginName'];
            try {
                $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
                $response = self::pushOperation(TransactionRequest::TRANS_WIN, $value, $user);
                $value['unlockType'] = 'CommitQueue';
                $value['operationId'] = $response->operation_id;
                $dataRes[] = $value;
            } catch (Exception $e) {
                AppLog::warning("One records was canceled. Cause: " . print_r($e->getMessage(), true) . " Data: " . print_r($value, true));
            }
        }
        return $dataRes;
    }

    static function pushOperation(string $typeOperation, array $data, IntegrationUser $user): TransactionResponse {
        $transactionRequest = new TransactionRequest(
                Config::get('integrations.microgaming.service_id'), $data['a:TransactionNumber'], $user->id, $user->getCurrency(), MicroGamingHelper::getTransactionDirection($typeOperation), TransactionHelper::amountCentsToWhole($data['a:ChangeAmount']), MicroGamingHelper::getTransactionType($typeOperation), $data['a:MgsReferenceNumber'], $data['a:GameName']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        return $transactionHandler->handle(new ProcessMicroGaming());
    }

}
