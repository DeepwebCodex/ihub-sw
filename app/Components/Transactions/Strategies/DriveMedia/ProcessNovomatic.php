<?php

namespace App\Components\Transactions\Strategies\DriveMedia;

use App\Components\Integrations\CodeMappingBase;
use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\DriveMediaNovomaticProdObjectIdMap;
use App\Models\Transactions;

/**
 * @property TransactionRequest $request
 * @property CodeMapping $codeMapping;
 */
class ProcessNovomatic extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    protected $CodeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array|null
     * @throws \App\Exceptions\Api\ApiHttpException
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

    /**
     * @param string $tradeId
     * @return int
     */
    protected function getObjectIdMap(string $tradeId): int
    {
        if (app()->environment() === 'production') {
            return DriveMediaNovomaticProdObjectIdMap::getObjectId($tradeId);
        }
        return hexdec(substr(md5($tradeId), 0, 15));
    }

    protected function setRequestObjectId()
    {
        if ($this->request->transaction_type === TransactionRequest::TRANS_BET) {
            $this->request->object_id = $this->getObjectIdMap($this->request->foreign_id);
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
