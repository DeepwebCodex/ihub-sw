<?php

namespace App\Components\Transactions\Strategies\MicroGaming;

use App\Components\Integrations\CodeMappingBase;
use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;
use App\Models\WirexGamingObjectIdMap;
use App\Models\WirexGamingProdObjectIdMap;

/**
 * @property TransactionRequest $request
 * @property CodeMapping $codeMapping;
 */
class ProcessWirexGaming extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    protected $codeMapping = CodeMapping::class;

    /**
     * @return Transactions
     */
    protected function getBetRecords()
    {
        $originalObjectId = $this->request->object_id;

        $this->request->object_id = $this->getObjectIdMap(
            $this->request->user_id,
            $this->request->currency,
            $originalObjectId
        );
        /**@var Transactions $betTransaction */
        return Transactions::getBetTransaction(
            $this->request->service_id,
            $this->request->user_id,
            $this->request->object_id,
            $this->request->partner_id
        );
    }

    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;
        $betTransaction = $this->getBetRecords();

        if ($this->request->transaction_type != TransactionRequest::TRANS_BET) {
            if (!$betTransaction) {
                $this->onHaveNotBet(
                    new ApiHttpException(
                        500,
                        null,
                        $this->codeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR)
                    )
                );
                return $this->responseData;
            }
        } elseif ($this->request->transaction_type == TransactionRequest::TRANS_BET) {
            //unique double bet
            if ($betTransaction && $betTransaction->foreign_id != $this->request->foreign_id) {
                $modifiedObjectId = $this->request->object_id;
                $this->request->object_id = $this->getObjectIdMapForDuplicate(
                    $this->request->user_id,
                    $this->request->currency,
                    $modifiedObjectId
                );
            }
        }

        if ($this->request->amount == 0) {
            return $this->processZeroAmountTransaction();
        }

        $lastRecord = Transactions::getTransaction(
            $this->request->service_id,
            $this->request->foreign_id,
            $this->request->transaction_type,
            $this->request->partner_id
        );

        $status = is_object($lastRecord) ? $lastRecord->status : null;

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
                $this->responseData = null;
                $this->isDuplicate = true;
                break;
            default:
                break;
        }

        return $this->responseData;
    }

    /**
     * @param ApiHttpException $exception
     * @return $this
     */
    protected function onTransactionDuplicate($exception)
    {
        $operation = $this->getAccountManager()->getOperations(
            $this->request->user_id,
            $this->request->direction,
            $this->request->object_id,
            $this->request->service_id
        );

        if (!$operation) {
            throw new ApiHttpException(
                409,
                'Finance error',
                $this->codeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR)
            );
        } elseif (count($operation) > 1) {
            throw new ApiHttpException(
                409,
                'Finance error, duplicated duplication',
                $this->codeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR)
            );
        }
        $this->responseData = $operation[0];
        $this->isDuplicate = true;
    }

    /**
     * @param \App\Exceptions\Api\GenericApiHttpException $exception
     */
    protected function onHaveNotBet($exception)
    {
        if ($this->request->transaction_type !== TransactionRequest::TRANS_REFUND) {
            parent::onHaveNotBet($exception);
            return;
        }
        $this->responseData = array_merge(
            $this->responseData,
            [
                'operation_id' => app('AccountManager')->getFreeOperationId()
            ]
        );
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param int $gameId
     * @return int
     */
    protected function getObjectIdMap(int $userId, string $currency, int $gameId): int
    {
        if (app()->environment() === 'production') {
            return WirexGamingProdObjectIdMap::getObjectId($userId, $currency, $gameId);
        }
        return WirexGamingObjectIdMap::getObjectId($userId, $currency, $gameId);
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param int $gameId
     * @return int
     */
    protected function getObjectIdMapForDuplicate(int $userId, string $currency, int $gameId): int
    {
        if (app()->environment() === 'production') {
            return WirexGamingProdObjectIdMap::getNextPrimaryIndex();
        }
        return WirexGamingObjectIdMap::getNextPrimaryIndex($userId, $currency, $gameId);
    }
}
