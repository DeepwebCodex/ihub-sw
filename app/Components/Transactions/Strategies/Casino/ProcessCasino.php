<?php

namespace App\Components\Transactions\Strategies\Casino;

use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Integrations\CodeMappingBase;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;

/**
 * @property  TransactionRequest $request
 */
class ProcessCasino extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;

        try {
            $this->responseData = $this->getAccountManager()->createTransaction(
                TransactionRequest::STATUS_COMPLETED,
                $request->service_id,
                $request->cashdesk_id,
                $request->user_id,
                $request->amount,
                $request->currency,
                $request->direction,
                $request->object_id,
                $request->comment,
                $request->partner_id,
                $request->client_ip
            );

            $this->writeTransaction();

            if(!$this->responseData){
                $this->onInvalidResponse();
            }

        } catch (ApiHttpException $e){
            $this->handleError($e);
        }

        return $this->responseData;
    }

    protected function onInvalidResponse()
    {
        throw new ApiHttpException(409, null, CodeMapping::getByMeaning(CodeMapping::INVALID_RESULT));
    }

    /**
     * @param ApiHttpException $e
     * @return $this
     */
    protected function onTransactionDuplicate($e)
    {
        $operation = $this->getAccountManager()->getOperations(
            $this->request->user_id,
            $this->request->direction,
            $this->request->object_id,
            $this->request->service_id);

        if(!$operation){
            $this->onInvalidResponse();
        }
        else if (count($operation) > 1)
        {
            throw new ApiHttpException(409, "Finance error, duplicated duplication", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }

        $this->responseData = $operation[0];

        $this->isDuplicate = true;
    }

    /**
     * @param ApiHttpException $e
     * @return $this
     */
    protected function onHaveNotBet($e)
    {
        if($this->request->transaction_type == TransactionRequest::TRANS_REFUND)
        {
            $this->responseData['operation_id'] = null;
            $this->isDuplicate = true;
        }

        throw $e;
    }

    /**
     * @param ApiHttpException $e
     */
    protected function onInsufficientFunds($e)
    {
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByMeaning(CodeMapping::NO_MONEY));
    }

    /**
     * @param ApiHttpException $e
     * @return bool
     */
    protected function onAccountDenied($e)
    {
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByMeaning(CodeMapping::INVALID_RESPONSE));
    }
}