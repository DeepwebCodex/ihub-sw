<?php

namespace App\Components\Transactions\Strategies\NetEntertainment;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Transactions\Strategies\Fundist\ProcessFundist;
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
    protected function process(TransactionRequest $request): array
    {
        $this->request = $request;
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
}
