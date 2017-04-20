<?php

namespace App\Components\Transactions\Strategies\Fundist;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property  TransactionRequest $request
 */
class ProcessFundist extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request):array
    {
        $this->request = $request;
        if ($this->request->transaction_type == TransactionRequest::TRANS_WIN) {
            $betTransaction = Transactions::getBetTransaction(
                $this->request->service_id,
                    $this->request->user_id,
                    $this->request->object_id,
                    $this->request->partner_id
            );
            if (!$betTransaction) {
                throw new ApiHttpException(Response::HTTP_OK, null, CodeMapping::getByErrorCode(StatusCode::BAD_OPERATION_ORDER));
            }
        }

        if ($this->request->amount == 0) {
            $this->processZeroAmountTransaction();
        } else {
            $this->processTransaction();
        }

        if ($this->responseData['operation_id'] === null) {
            throw new ApiHttpException(Response::HTTP_REQUEST_TIMEOUT, null, CodeMapping::getByErrorCode(StatusCode::UNKNOWN));
        }

        return $this->responseData;
    }

    /**
     * @return array
     */
    private function processTransaction():array
    {
        $lastRecord = Transactions::getTransaction(
            $this->request->service_id,
                $this->request->foreign_id,
                $this->request->transaction_type,
                $this->request->partner_id
        );

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

    protected function onTransactionDuplicate($e)
    {
        if($this->request->transaction_type == TransactionRequest::TRANS_WIN){
            throw new ApiHttpException(Response::HTTP_OK, null, CodeMapping::getByErrorCode(StatusCode::DUPLICATED_WIN));
        }

        throw new ApiHttpException(Response::HTTP_OK, null, CodeMapping::getByErrorCode(StatusCode::DUPLICATED_TRANSACTION));
    }

    protected function onInsufficientFunds($e)
    {
        throw new ApiHttpException(Response::HTTP_OK, null, CodeMapping::getByErrorCode(StatusCode::INSUFFICIENT_FUNDS));
    }
}