<?php

namespace App\Components\Transactions\Strategies\EuroGamesTech;


use App\Components\ExternalServices\AccountManager;
use App\Components\Integrations\EuroGamesTech\CodeMapping;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;

/**
 * @property  TransactionRequest $request
 */
class ProcessEuroGamesTech implements TransactionProcessorInterface
{

    private $request;

    private $responseData = [];
    private $isDuplicate = false;

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
                throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
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
                    if($this->runPending()) {
                        $this->runCompleted($this->responseData['operation_id']);
                    }
                break;
            case TransactionRequest::STATUS_PENDING:
                    $this->runCompleted($lastRecord->operation_id);
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

    private function runPending()
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

        } catch (ApiHttpException $e){
            $this->handleError($e);
        }

        return $this->writeTransaction();
    }

    private function runCompleted(int $operationId)
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

        } catch (ApiHttpException $e){
            $this->handleError($e);
        }

        return $this->writeTransaction();
    }

    private function writeTransaction(){
        $model = Transactions::create(array_merge($this->responseData, [
            'foreign_id' => $this->request->foreign_id,
            'transaction_type' => $this->request->transaction_type
        ]));

        if(!$model) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function processZeroAmountTransaction(){
        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type);

        $status = is_object($lastRecord) ? $lastRecord->status : null;

        switch ($status){
            case TransactionRequest::STATUS_NULL:
            case TransactionRequest::STATUS_PENDING:
                $model = Transactions::create([
                    'user_id' => $this->request->user_id,
                    'service_id' => $this->request->service_id,
                    'amount' => $this->request->amount,
                    'move'  => $this->request->direction,
                    'partner_id' => request()->server('PARTNER_ID'),
                    'cashdesk' => $this->request->cashdesk_id,
                    'payment_instrument_id' => null,
                    'wallet_id' => null,
                    'wallet_account_id' => null,
                    'status' => TransactionRequest::STATUS_COMPLETED,
                    'currency' => $this->request->currency,
                    'foreign_id' => $this->request->foreign_id,
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
     * @param ApiHttpException $e
     * @return bool
     */
    protected function handleError($e)
    {
        $errorCode = (int) $e->getPayload('code');

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
        throw new ApiHttpException(409, "Invalid response", CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @param ApiHttpException $e
     * @return $this
     */
    protected function onTransactionDuplicate($e)
    {
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByMeaning(CodeMapping::DUPLICATE));
    }

    /**
     * @param ApiHttpException $e
     * @return $this
     */
    protected function onHaveNotBet($e)
    {
        throw new ApiHttpException($e->getStatusCode(), "Invalid operation order", CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
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
        throw new ApiHttpException($e->getStatusCode(), "Account denied", CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @return AccountManager
     */
    protected function getAccountManager()
    {
        return app('AccountManager');
    }
}