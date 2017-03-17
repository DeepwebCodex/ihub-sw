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
use App\Components\Users\Interfaces\UserInterface;
use App\Facades\AppLog;
use Exception;
use Illuminate\Support\Facades\Config;

/**
 * Description of Process
 *
 * @author petroff
 */
class CommitRollbackProcessor implements IOperationsProcessor
{

    //RollbackQueue

    protected $unlockType;
    protected $transType;

    function __construct(string $unlockType, string $transType)
    {
        $this->unlockType = $unlockType;
        $this->transType = $transType;
    }

    public function make(array $data): array
    {
        $dataRes = array();
        foreach ($data as $key => $value) {
            $user_id = (int) $value['a:LoginName'];
            try {
                $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
                $response = $this->pushOperation($this->transType, $value, $user);
                $value['unlockType'] = $this->unlockType;
                $value['operationId'] = $response->operation_id;
                $value['isDuplicate'] = $response->isDuplicate;
                $dataRes[] = $value;
            } catch (Exception $e) {
                AppLog::warning("One records was canceled. Cause: " . print_r($e->getMessage(), true) . " Data: " . print_r($value, true));
            }
        }

        return $dataRes;
    }

    public function pushOperation(string $typeOperation, array $data, UserInterface $user): TransactionResponse
    {
        
        $transactionRequest = new TransactionRequest(
                Config::get('integrations.microgaming.service_id'), 
                $data['a:TransactionNumber'], 
                $user->id, $user->getCurrency(), 
                MicroGamingHelper::getTransactionDirection($typeOperation), 
                TransactionHelper::amountCentsToWhole($data['a:ChangeAmount']), 
                MicroGamingHelper::getTransactionType($typeOperation), 
                $data['a:MgsReferenceNumber'], 
                $data['a:GameName']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        return $transactionHandler->handle(new ProcessMicroGaming());
    }

}
