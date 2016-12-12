<?php

namespace App\Components\Transactions\Strategies\BetGames;

use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;

/**
 * @property  TransactionRequest $request
 */
class ProcessBetGames extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{

    /**
     * @param TransactionRequest $request
     * @return array
     */
    public function process(TransactionRequest $request):array
    {
        $this->request = $request;

        if ($this->request->transaction_type != TransactionRequest::TRANS_BET) {
            $betTransaction = Transactions::getBetTransaction($this->request->service_id, $this->request->user_id, $this->request->object_id);

            if (!$betTransaction) {
                throw new ApiHttpException(500, null, ['code' => TransactionHelper::getTransactionErrorCode(TransactionHelper::BAD_OPERATION_ORDER)]);
            }
        }

        if ($this->request->amount == 0) {
            $this->processZeroAmountTransaction();
        } else {
            $this->processTransaction();
        }

        if ($this->responseData['operation_id'] === null) {
            throw new ApiHttpException(500, null, ['code' => TransactionHelper::UNKNOWN]);
        }

        return $this->responseData;
    }

    /**
     * @return array
     */
    private function processTransaction():array
    {
        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type);

        $status = $lastRecord->status ?? null;

        switch ($status) {
            case TransactionRequest::STATUS_NULL:
                if ($newRecord = $this->runPending()) {
                    $this->runCompleted($this->responseData['operation_id'], $newRecord);
                }
                break;
            case TransactionRequest::STATUS_PENDING:
                $this->runCompleted($lastRecord->operation_id, $lastRecord);
                break;
            case TransactionRequest::STATUS_COMPLETED:
                $this->responseData = $lastRecord->attributesToArray();
                $this->isDuplicate = true;
                break;
            case TransactionRequest::STATUS_CANCELED:
                $this->responseData = [];
                $this->isDuplicate = true;
                break;
            default:
                break;
        }

        return $this->responseData;
    }
}