<?php

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Integrations\CodeMappingBase;
use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\CommonSerial;
use App\Models\Transactions;

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
        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type, $this->request->partner_id);
        if (!$lastRecord) {
            $this->request->object_id = CommonSerial::getSerial();
        }
        return parent::process($lastRecord);
    }

    protected function onInsufficientFunds($e)
    {
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByErrorCode
                (StatusCode::FAIL_BALANCE));
    }

}
