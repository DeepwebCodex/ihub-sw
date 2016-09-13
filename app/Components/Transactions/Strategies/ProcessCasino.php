<?php

namespace App\Components\Transactions\Strategies;


use App\Components\ExternalServices\AccountManager;
use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;

/**
 * @property  TransactionRequest $request
 */
class ProcessCasino implements TransactionProcessorInterface
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
                $request->comment
            );

            if(!$this->responseData){
                $this->onInvalidResponse();
            }

        } catch (ApiHttpException $e){
            $this->handleError($e);
        }

        return $this->responseData;
    }

    /**
     * @return array
     */
    public function getTransactionData(){
        return $this->responseData;
    }

    /**
     * @return bool
     */
    public function isDuplicate(){
        return $this->isDuplicate;
    }

    /**
     * @param ApiHttpException $e
     * @return bool
     */
    protected function handleError($e){
        $errorCode = (int) $e->getPayload('code');

        switch ($errorCode){
            case 1402:
                return $this->onTransactionDuplicate($e);
                break;
            case 1403:
                return $this->onHaveNotBet($e);
                break;
            case 1027:
                return $this->onInsufficientFunds($e);
                break;
            case 1020:
            case -2:
                return $this->onAccountDenied($e);
                break;
            default:
                throw $e;
        }
    }

    protected function onInvalidResponse(){
        throw new ApiHttpException(409, null, CodeMapping::getByMeaning(CodeMapping::INVALID_RESULT));
    }

    /**
     * @param ApiHttpException $e
     * @return $this
     */
    protected function onTransactionDuplicate($e){
        $operation = $this->getAccountManager()->getOperations(
            $this->request->user_id,
            $this->request->direction,
            $this->request->object_id,
            $this->request->service_id);

        if(!$operation){
            $this->onInvalidResponse();
        }

        $this->responseData = $operation;
        $this->isDuplicate = true;
    }

    /**
     * @param ApiHttpException $e
     * @return $this
     */
    protected function onHaveNotBet($e){
        $this->isDuplicate = true;
    }

    /**
     * @param ApiHttpException $e
     */
    protected function onInsufficientFunds($e){
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByMeaning(CodeMapping::NO_MONEY));
    }

    /**
     * @param ApiHttpException $e
     * @return bool
     */
    protected function onAccountDenied($e){
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByMeaning(CodeMapping::INVALID_RESPONSE));
    }

    /**
     * @return AccountManager
     */
    protected function getAccountManager(){
        return app('AccountManager');
    }
}