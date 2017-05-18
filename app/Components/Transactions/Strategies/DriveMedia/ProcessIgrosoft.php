<?php

namespace App\Components\Transactions\Strategies\DriveMedia;

use App\Components\Integrations\DriveMedia\CodeMapping;
use iHubGrid\SeamlessWalletCore\Transactions\BaseSeamlessWalletProcessor;
use iHubGrid\SeamlessWalletCore\Transactions\Interfaces\TransactionProcessorInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class ProcessIgrosoft extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    protected $codeMapping = CodeMapping::class;

    protected function process(TransactionRequest $request)
    {
        $this->request = $request;

        if ($this->request->transaction_type != TransactionRequest::TRANS_BET) {
            $betTransaction = Transactions::getLastBetByUserWithForeignId($this->request->service_id, $this->request->user_id, $this->request->partner_id, $this->request->game_id, $this->request->foreign_id);
            if(!$betTransaction) {
                throw new ApiHttpException(200, null, ($this->codeMapping)::getByMeaning(CodeMapping::SERVER_ERROR));
            }

            $this->request->object_id = $betTransaction->object_id;
        }

        if($this->request->amount == 0)
        {
            return $this->processZeroAmountTransaction();
        }

        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type, $this->request->partner_id);

        $status = is_object($lastRecord) ? $lastRecord->status : null;

        switch ($status){
            case TransactionRequest::STATUS_NULL:
                if($newRecord = $this->runPending()) {
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
                $this->responseData = null;
                $this->isDuplicate = true;
                break;
            default:
                break;
        }

        return $this->responseData;

    }
}