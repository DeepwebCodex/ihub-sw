<?php

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;
use function app;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class Withdrawal extends TransactionProcessor
{

    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request)
    {
        $this->request = $request;
        $refund = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, TransactionRequest::TRANS_REFUND, $this->request->partner_id);
        if ($refund) {
            throw new ApiHttpException(500, null, CodeMapping::getByErrorCode(StatusCode::BAD_ORDER));
        }

        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type, $this->request->partner_id);
        if (!$lastRecord) {
            $this->request->object_id = app('AccountManager')->getFreeOperationId();
        }
        return parent::make($lastRecord);
    }

    protected function onInsufficientFunds($e)
    {
        throw new ApiHttpException(402, null, CodeMapping::getByErrorCode(StatusCode::INSUFFICIENT_FUNDS));
    }

}
