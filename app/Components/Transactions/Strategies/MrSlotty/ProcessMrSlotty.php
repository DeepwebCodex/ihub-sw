<?php

namespace App\Components\Transactions\Strategies\MrSlotty;


use App\Components\Integrations\MrSlotty\StatusCode;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Components\Integrations\MrSlotty\CodeMapping;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;

/**
 * Class ProcessMrSlotty
 * @package App\Components\Transactions\Strategies\MrSlotty
 */
class ProcessMrSlotty extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    /**
     * @param TransactionRequest $request
     * @return array|null
     */
    public function process(TransactionRequest $request)
    {
        $this->request = $request;

        if($this->request->transaction_type != TransactionRequest::TRANS_BET)
        {
            $betTransaction = Transactions::getBetTransaction(
                $this->request->service_id,
                $this->request->user_id,
                $this->request->object_id
            );

            if(!$betTransaction)
            {
                throw new ApiHttpException(500, "Bet was not placed.", CodeMapping::getByMeaning(StatusCode::INTERNAL_SERVER_ERROR));
            }
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