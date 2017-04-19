<?php

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of Transaction
 *
 * @author petroff
 */
abstract class TransactionProcessor extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{

    protected function process(TransactionRequest $lastRecord = null)
    {

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

    protected function onTransactionDuplicate($e)
    {
        $operation = $this->getAccountManager()->getOperations(
                $this->request->user_id, $this->request->direction, $this->request->object_id, $this->request->service_id);

        if (!$operation) {
            throw new ApiHttpException(409, "Finance error", CodeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR));
        } else if (count($operation) != count($operation, COUNT_RECURSIVE)) {
            throw new ApiHttpException(409, "Finance error, duplicated duplication", CodeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }

        $this->responseData = $operation;
        $this->isDuplicate = true;
    }

}
