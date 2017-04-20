<?php

namespace App\Components\Transactions\Strategies\MicroGaming;

use iHubGrid\ErrorHandler\Http\CodeMappingBase;
use App\Components\Integrations\MicroGaming\CodeMapping;
use iHubGrid\SeamlessWalletCore\Transactions\BaseSeamlessWalletProcessor;
use iHubGrid\SeamlessWalletCore\Transactions\Interfaces\TransactionProcessorInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Models\MicroGamingObjectIdMap;
use App\Models\MicroGamingProdObjectIdMap;
use iHubGrid\SeamlessWalletCore\Models\Transactions;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class ProcessMicroGaming extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{

    protected $codeMapping = CodeMapping::class;

    
    
    protected function getBetRecords()
    {
        $originalObjectId = $this->request->object_id;

        $this->request->object_id = $this->getObjectIdMap(
            $this->request->user_id,
            $this->request->currency,
            $originalObjectId
        );

        /**@var Transactions $betTransaction*/
        return Transactions::getBetTransaction($this->request->service_id, $this->request->user_id, $this->request->object_id, $this->request->partner_id);
    }
    
    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;
        $betTransaction = $this->getBetRecords();
        
        if($this->request->transaction_type != TransactionRequest::TRANS_BET)
        {
            if(!$betTransaction){
                $this->onHaveNotBet(new ApiHttpException(500, null, ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR)));
                return $this->responseData;
            }
        } elseif ($this->request->transaction_type == TransactionRequest::TRANS_BET) {
            //unique double bet
            if($betTransaction && $betTransaction->foreign_id != $this->request->foreign_id) {
                $modifiedObjectId = $this->request->object_id;
                $this->request->object_id = $this->getObjectIdMapForDuplicate(
                    $this->request->user_id,
                    $this->request->currency,
                    $modifiedObjectId);
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

        if(!$operation)
        {
            throw new ApiHttpException(409, "Finance error", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }
        else if (count($operation) > 1)
        {
            throw new ApiHttpException(409, "Finance error, duplicated duplication", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }

        $this->responseData = $operation[0];
        $this->isDuplicate = true;
    }

    protected function onHaveNotBet($e)
    {
        if($this->request->transaction_type !== TransactionRequest::TRANS_REFUND) {
            parent::onHaveNotBet($e);
        }else {
            $this->responseData = array_merge($this->responseData, [
                'operation_id' => app('AccountManager')->getFreeOperationId()
            ]);
        }
    }

    /**
     * На данный момент для мапинга игровых раундов микрогейминга мы используем две схемы:
     *  - автоинкремент для прода
     *  - числовой хеш для дева (не на проде поскольку после 1ккк транзакций шанс пересечения 50% для дева спасает нас от гемороя
     *  на проде - бомба замедленного действия)
     *
     * @param int $user_id
     * @param string $currency
     * @param int $game_id
     * @return int
     */
    protected function getObjectIdMap(int $user_id, string $currency, int $game_id) : int
    {
        if(app()->environment() == 'production')
        {
            return MicroGamingProdObjectIdMap::getObjectId($user_id, $currency, $game_id);
        }

        return MicroGamingObjectIdMap::getObjectId(
            $user_id,
            $currency,
            $game_id
        );
    }

    protected function getObjectIdMapForDuplicate(int $user_id, string $currency, int $game_id) : int
    {
        if(app()->environment() == 'production')
        {
            return MicroGamingProdObjectIdMap::getNextPrimaryIndex();
        }

        return MicroGamingObjectIdMap::getNextPrimaryIndex(
            $user_id,
            $currency,
            $game_id
        );
    }
}