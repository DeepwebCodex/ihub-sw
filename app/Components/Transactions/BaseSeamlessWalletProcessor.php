<?php


namespace App\Components\Transactions;


use App\Components\ExternalServices\AccountManager;
use App\Components\Integrations\CodeMappingBase;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\GenericApiHttpException;
use App\Models\Transactions;
/**
 * @property  CodeMappingBase $codeMapping;
 * @property  TransactionRequest $request;
 */
class BaseSeamlessWalletProcessor
{
    /**@var CodeMappingBase */
    protected $codeMapping;

    protected $request;

    protected $responseData = [];
    protected $isDuplicate = false;

    protected function runPending()
    {
        try {
            $this->responseData = $this->getAccountManager()->createTransaction(
                TransactionRequest::STATUS_PENDING,
                $this->request->service_id,
                $this->request->cashdesk_id,
                $this->request->user_id,
                $this->request->amount,
                $this->request->currency,
                $this->request->direction,
                $this->request->object_id,
                $this->request->comment
            );

            if(!$this->responseData){
                $this->onInvalidResponse();
            }

        } catch (GenericApiHttpException $e){
            $this->handleError($e);
        }

        return $this->writeTransaction();
    }

    /**
     * @return array
     */
    protected function processZeroAmountTransaction(){
        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type);

        $status = is_object($lastRecord) ? $lastRecord->status : null;

        switch ($status){
            case TransactionRequest::STATUS_NULL:
            case TransactionRequest::STATUS_PENDING:
                $model = Transactions::create([
                    'operation_id' => app('AccountManager')->getFreeOperationId(),
                    'user_id' => $this->request->user_id,
                    'service_id' => $this->request->service_id,
                    'amount' => $this->request->amount,
                    'move'  => $this->request->direction,
                    'partner_id' => request()->server('PARTNER_ID'),
                    'cashdesk' => $this->request->cashdesk_id,
                    'status' => TransactionRequest::STATUS_COMPLETED,
                    'currency' => $this->request->currency,
                    'foreign_id' => $this->request->foreign_id,
                    'object_id' => $this->request->object_id,
                    'transaction_type' => $this->request->transaction_type
                ]);
                if($model){
                    $this->responseData = $model->attributesToArray();
                }
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

    /**
     * @param int $operationId
     * @param Transactions $lastRecord
     * @return bool
     */
    protected function runCompleted(int $operationId, $lastRecord)
    {
        try {
            $this->responseData = $this->getAccountManager()->commitTransaction(
                $this->request->user_id,
                $operationId,
                $this->request->direction,
                $this->request->object_id,
                $this->request->currency,
                $this->request->comment
            );

            if(!$this->responseData){
                $this->onInvalidResponse();
            }

        } catch (GenericApiHttpException $e){
            $this->handleError($e);
        }

        return $this->writeTransaction($lastRecord, TransactionRequest::STATUS_COMPLETED);
    }

    /**
     * @param Transactions $model
     * @param string $newStatus
     * @return bool|Transactions
     */
    protected function writeTransaction($model = null, string $newStatus = null){

        if($model && $newStatus && $newStatus != $model->status){
            $model->status = $newStatus;

            return $model->save();
        }

        $model = Transactions::create(array_merge($this->responseData, [
            'object_id'  => $this->request->object_id,
            'foreign_id' => $this->request->foreign_id,
            'transaction_type' => $this->request->transaction_type
        ]));

        if(!$model) {
            return false;
        }

        return $model;
    }

    /**
     * @return array
     */
    public function getTransactionData()
    {
        return $this->responseData;
    }

    /**
     * @return bool
     */
    public function isDuplicate()
    {
        return $this->isDuplicate;
    }

    /**
     * @param GenericApiHttpException $e
     * @return bool
     */
    protected function handleError($e)
    {
        $errorCode = (int) $e->getCode();

        switch (TransactionHelper::getTransactionErrorState($errorCode))
        {
            case TransactionHelper::DUPLICATE:
                return $this->onTransactionDuplicate($e);
            case TransactionHelper::BAD_OPERATION_ORDER:
                return $this->onHaveNotBet($e);
            case TransactionHelper::INSUFFICIENT_FUNDS:
                return $this->onInsufficientFunds($e);
            case TransactionHelper::ACCOUNT_DENIED:
                return $this->onAccountDenied($e);
            default:
                throw $e;
        }
    }

    protected function onInvalidResponse()
    {
        throw new ApiHttpException(409, "Invalid response", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
    }

    /**
     * @param GenericApiHttpException $e
     * @return $this
     */
    protected function onTransactionDuplicate($e)
    {
        throw new ApiHttpException($e->getStatusCode(), null, ($this->codeMapping)::getByMeaning(CodeMappingBase::DUPLICATE));
    }

    /**
     * @param GenericApiHttpException $e
     * @return $this
     */
    protected function onHaveNotBet($e)
    {
        throw new ApiHttpException($e->getStatusCode(), "Invalid operation order", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
    }

    /**
     * @param GenericApiHttpException $e
     */
    protected function onInsufficientFunds($e)
    {
        throw new ApiHttpException($e->getStatusCode(), null, ($this->codeMapping)::getByMeaning(CodeMappingBase::NO_MONEY));
    }

    /**
     * @param GenericApiHttpException $e
     * @return bool
     */
    protected function onAccountDenied($e)
    {
        throw new ApiHttpException($e->getStatusCode(), "Account denied", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
    }

    /**
     * @return AccountManager
     */
    protected function getAccountManager()
    {
        return app('AccountManager');
    }
}