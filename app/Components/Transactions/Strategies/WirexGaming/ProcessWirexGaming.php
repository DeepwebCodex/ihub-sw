<?php

namespace App\Components\Transactions\Strategies\MicroGaming;

use App\Components\Integrations\MicroGaming\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\BaseSeamlessWalletProcessor;
use iHubGrid\SeamlessWalletCore\Transactions\Interfaces\TransactionProcessorInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Http\Response;

/**
 * @property TransactionRequest $request
 * @property CodeMapping $codeMapping;
 */
class ProcessWirexGaming extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface
{
    /**
     * @var string
     */
    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    protected function process(TransactionRequest $request): array
    {
        $this->request = $request;
        if (in_array(
            $this->request->transaction_type,
            [TransactionRequest::TRANS_WIN, TransactionRequest::TRANS_REFUND]
        )) {
            $betTransaction = Transactions::getBetTransaction(
                $this->request->service_id,
                $this->request->user_id,
                $this->request->object_id,
                $this->request->partner_id
            );
            if (!$betTransaction) {
                throw new ApiHttpException(
                    Response::HTTP_OK,
                    null,
                    CodeMapping::getByErrorCode(CodeMapping::SERVER_ERROR)
                );
            }
        }
        if ($this->request->amount == 0) {
            $this->processZeroAmountTransaction();
        } else {
            $this->processTransaction();
        }
        if ($this->responseData['operation_id'] === null) {
            throw new ApiHttpException(
                Response::HTTP_REQUEST_TIMEOUT,
                null,
                CodeMapping::getByErrorCode(CodeMapping::SERVER_ERROR)
            );
        }
        return $this->responseData;
    }

    /**
     * @return array
     */
    private function processTransaction(): array
    {
        $lastRecord = Transactions::getTransaction(
            $this->request->service_id,
            $this->request->foreign_id,
            $this->request->transaction_type,
            $this->request->partner_id
        );

        $status = $lastRecord->status ?? null;

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
                $this->responseData = [];
                $this->isDuplicate = true;
                break;
            default:
                break;
        }

        return $this->responseData;
    }

    /**
     * @param \iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException $e
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    protected function onTransactionDuplicate($e)
    {
        if ($this->request->transaction_type == TransactionRequest::TRANS_WIN) {
            throw new ApiHttpException(
                Response::HTTP_OK,
                null,
                CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR)
            );
        }
        throw new ApiHttpException(
            Response::HTTP_OK,
            null,
            CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR)
        );
    }

    /**
     * @param \iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException $e
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    protected function onInsufficientFunds($e)
    {
        throw new ApiHttpException(
            Response::HTTP_OK,
            null,
            CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR)
        );
    }
}
