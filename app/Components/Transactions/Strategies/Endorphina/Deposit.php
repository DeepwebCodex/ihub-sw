<?php

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Transactions\Strategies\Endorphina\TransactionProcessor;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class Deposit extends TransactionProcessor
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
            $transactionBet = Transactions::getLastBetTransaction($this->request->service_id, $this->request->user_id, $this->request->currency, $this->request->partner_id);

            if (!$transactionBet) {
                throw new ApiHttpException(500, null, CodeMapping::getByErrorCode(StatusCode::BAD_ORDER));
            }
            $this->request->object_id = $transactionBet->object_id;
        }

        return parent::make($lastRecord);
    }

}
