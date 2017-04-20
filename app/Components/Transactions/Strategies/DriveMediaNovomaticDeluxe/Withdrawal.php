<?php

namespace App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe;

use iHubGrid\ErrorHandler\Http\CodeMappingBase;
use App\Components\Integrations\DriveMediaNovomaticDeluxe\CodeMapping;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Models\CommonSerial;
use iHubGrid\SeamlessWalletCore\Models\Transactions;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class Withdrawal extends TransactionProcessor {

    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request) {
        $this->request = $request;
        $lastRecord = Transactions::getTransaction($this->request->service_id,
                        $this->request->foreign_id,
                        $this->request->transaction_type,
                        $this->request->partner_id);
        if (!$lastRecord) {
            $this->request->object_id = CommonSerial::getSerial();
        }

        return $this->make($lastRecord);
    }

    protected function onTransactionDuplicate($e) {
        $operation = $this->getAccountManager()->getOperations(
                $this->request->user_id, $this->request->direction,
                $this->request->object_id, $this->request->service_id);

        if (!$operation) {
            throw new ApiHttpException(409, "Finance error", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
        } else if (count($operation) != count($operation, COUNT_RECURSIVE)) {
            throw new ApiHttpException(409, "Finance error, duplicated duplication", ($this->codeMapping)::getByMeaning(CodeMappingBase::SERVER_ERROR));
        }

        $this->responseData = $operation;
        $this->isDuplicate = true;
    }

}
