<?php

namespace App\Components\Transactions\Strategies\DriveMedia;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Models\DriveMediaPlaytechProdObjectIdMap;
use App\Models\Transactions;
use App\Exceptions\Api\ApiHttpException;

class ProcessPlaytech extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{

    protected $CodeMapping = CodeMapping::class;

    protected function process(TransactionRequest $request)
    {
        $this->request = $request;

        if($this->request->transaction_type == TransactionRequest::TRANS_BET)
        {
            $this->request->object_id = $this->getObjectIdMap($this->request->foreign_id);
        } else {
            $betTransaction = Transactions::getLastBetByUser($this->request->service_id, $this->request->user_id, $this->request->partner_id, $this->request->game_id);
            if(!$betTransaction) {
                throw new ApiHttpException(200, null, ($this->CodeMapping)::getByMeaning(CodeMapping::SERVER_ERROR));
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

    protected function getObjectIdMap(string $trade_id):int
    {
        if(app()->environment() == 'production')
        {
            return DriveMediaPlaytechProdObjectIdMap::getObjectId($trade_id);
        }

        return hexdec(substr(md5($trade_id), 0, 15));
    }

}