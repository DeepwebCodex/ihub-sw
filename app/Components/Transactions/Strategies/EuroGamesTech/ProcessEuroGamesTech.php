<?php

namespace App\Components\Transactions\Strategies\EuroGamesTech;

use App\Components\Integrations\CodeMappingBase;
use App\Components\Integrations\EuroGamesTech\CodeMapping;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class ProcessEuroGamesTech extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{

    protected $codeMapping = CodeMapping::class;
    /**
     * @param TransactionRequest $request
     * @return array
     */
    public function process(TransactionRequest $request)
    {
        $this->request = $request;

        if($this->request->transaction_type != TransactionRequest::TRANS_BET)
        {
            $betTransaction = Transactions::getBetTransaction($this->request->service_id, $this->request->user_id, $this->request->object_id);

            if(!$betTransaction){
                throw new ApiHttpException(500, "Bet was not placed", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
            }
        }

        if($this->request->amount == 0)
        {
            return $this->processZeroAmountTransaction();
        }

        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type);

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
                    $this->responseData = $lastRecord->attributesToArray();
                    $this->isDuplicate = true;
                break;
            default:
                break;
        }

        return $this->responseData;
    }
}