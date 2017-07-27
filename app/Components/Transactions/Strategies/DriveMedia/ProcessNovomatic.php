<?php

namespace App\Components\Transactions\Strategies\DriveMedia;

use App\Components\Integrations\DriveMediaNovomatic\CodeMapping;
use iHubGrid\ErrorHandler\Http\CodeMappingBase;
use iHubGrid\SeamlessWalletCore\Transactions\BaseSeamlessWalletProcessor;
use iHubGrid\SeamlessWalletCore\Transactions\Interfaces\TransactionProcessorInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;

/**
 * @property TransactionRequest $request
 * @property CodeMapping $codeMapping;
 */
class ProcessNovomatic extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array|null
     * @throws ApiHttpException
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;
        $this->setRequestObjectId();
        if ($this->request->amount == 0) {
            return $this->processZeroAmountTransaction();
        }
        $lastTransactionRecord = Transactions::getTransaction(
            $this->request->service_id,
            $this->request->foreign_id,
            $this->request->transaction_type,
            $this->request->partner_id
        );
        $status = is_object($lastTransactionRecord) ? $lastTransactionRecord->status : null;
        switch ($status) {
            case TransactionRequest::STATUS_NULL:
                if ($newRecord = $this->runPending()) {
                    $this->runCompleted($this->responseData['operation_id'], $newRecord);
                }
                break;
            case TransactionRequest::STATUS_PENDING:
                $this->runCompleted($lastTransactionRecord->operation_id, $lastTransactionRecord);
                break;
            case TransactionRequest::STATUS_COMPLETED:
                $this->responseData = $lastTransactionRecord->attributesToArray();
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

    protected function setRequestObjectId()
    {
        if ($this->request->transaction_type === TransactionRequest::TRANS_BET) {
            return;
        }
        $betTransaction = Transactions::getLastBetByUser(
            $this->request->service_id,
            $this->request->user_id,
            $this->request->partner_id,
            $this->request->game_id
        );
        if (!$betTransaction) {
            throw new ApiHttpException(500, null, ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }
        $this->request->object_id = $betTransaction->object_id;
    }
}
