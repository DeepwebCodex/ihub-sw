<?php

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\CodeMappingBase;
use iHubGrid\SeamlessWalletCore\Transactions\BaseSeamlessWalletProcessor;
use iHubGrid\SeamlessWalletCore\Transactions\Interfaces\TransactionProcessorInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;
use function GuzzleHttp\json_encode;

/**
 * Description of Transaction
 *
 * @author petroff
 */
abstract class TransactionProcessor extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{

    protected function make(Model $lastRecord = null)
    {
        $this->request->comment = json_encode($this->request->getComment()); //override zeo object id
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
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR));
        } else if (count($operation) > 1) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }

        $this->responseData = $operation[0];
        $this->isDuplicate = true;
    }


}
