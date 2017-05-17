<?php

namespace App\Components\Transactions\Strategies\NetEntertainment;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Transactions\Strategies\Fundist\ProcessFundist;
use App\Models\NetEntertainmentObjectIdMap;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Http\Response;

/**
 * Class ProcessNetEntertainment
 * @package App\Components\Transactions\Strategies\NetEntertainment
 */
class ProcessNetEntertainment extends ProcessFundist
{
    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array|null
     * @throws ApiHttpException
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;
        $this->setRequestObjectId();
        if ($this->request->amount == 0) {
            return $this->processZeroAmountTransaction();
        }
        $lastTransactionRecord = Transactions::getTransaction(
            $this->request->service_id,
            $this->request->foreign_id,
            $this->request->transaction_type,
            $this->request->partner_id
        );
        $status = is_object($lastTransactionRecord) ? $lastTransactionRecord->status : null;
        switch ($status) {
            case TransactionRequest::STATUS_NULL:
                if ($newRecord = $this->runPending()) {
                    $this->runCompleted($this->responseData['operation_id'], $newRecord);
                }
                break;
            case TransactionRequest::STATUS_PENDING:
                $this->runCompleted($lastTransactionRecord->operation_id, $lastTransactionRecord);
                break;
            case TransactionRequest::STATUS_COMPLETED:
                $this->responseData = $lastTransactionRecord->attributesToArray();
                $this->isDuplicate = true;
                break;
            case TransactionRequest::STATUS_CANCELED:
                $this->responseData = [];
                $this->isDuplicate = true;
                break;
            default:
                break;
        }
        if ($this->responseData['operation_id'] === null) {
            throw new ApiHttpException(
                Response::HTTP_REQUEST_TIMEOUT,
                null,
                CodeMapping::getByErrorCode(StatusCode::UNKNOWN)
            );
        }
        return $this->responseData;
    }

    protected function setRequestObjectId()
    {
        if ($this->request->transaction_type === TransactionRequest::TRANS_BET) {
            //KOLOK: для ставок object_id и foreign_id поменяны местами, чтобы прокинуть i_actionid
            list($gameId, $actionId) = explode(':', $this->request->foreign_id);
            $this->request->foreign_id = $this->request->object_id;
            $this->request->object_id = $this->getObjectIdMap($gameId, $actionId);
            return;
        }
    }

    /**
     * @param int $gameId
     * @param string $actionId
     * @return int
     */
    protected function getObjectIdMap(int $gameId, string $actionId): int
    {
        return NetEntertainmentObjectIdMap::getObjectId($gameId, $actionId);
    }
}
