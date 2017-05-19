<?php

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use function app;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class Refund extends TransactionProcessor
{

    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;
        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type, $this->request->partner_id);

        if (!$lastRecord) {
            $transactionBet = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, TransactionRequest::TRANS_BET, $this->request->partner_id);
            if (!$transactionBet) {
                $operationId = app('AccountManager')->getFreeOperationId();
                Transactions::create([
                    'object_id' => $operationId,
                    'foreign_id' => $this->request->foreign_id,
                    'transaction_type' => $this->request->transaction_type,
                    'game_id' => $this->request->game_id,
                    'client_ip' => $this->request->client_ip,
                    'user_id' => $this->request->user_id,
                    'operation_id' => $operationId,
                    'service_id' => $this->request->service_id,
                    'amount' => $this->request->amount,
                    'move' => $this->request->direction,
                    'partner_id' => $this->request->partner_id,
                    'cashdesk' => $this->request->cashdesk_id,
                    'status' => TransactionRequest::STATUS_COMPLETED,
                    'currency' => $this->request->currency,
                    'foreign_id' => $this->request->foreign_id
                ]);
                $this->responseData = array_merge($this->responseData, [
                    'operation_id' => $operationId
                ]);
                return $this->responseData;
            }

            $this->request->object_id = $transactionBet->object_id;
        }

        return parent::make($lastRecord);
    }

}
