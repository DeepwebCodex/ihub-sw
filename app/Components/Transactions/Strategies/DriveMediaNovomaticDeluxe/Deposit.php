<?php

namespace App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe;

use App\Components\Integrations\DriveMediaNovomaticDeluxe\CodeMapping;
use App\Components\Integrations\DriveMediaNovomaticDeluxe\StatusCode;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Transactions;
use App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe\TransactionProcessor;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class Deposit extends TransactionProcessor {

    protected $codeMapping = CodeMapping::class;

    /**
     * @param TransactionRequest $request
     * @return array
     */
    protected function process(TransactionRequest $request) {
        $this->request = $request;
        $lastRecord = Transactions::getTransaction($this->request->service_id, $this->request->foreign_id, $this->request->transaction_type, $this->request->partner_id);
        if (!$this->request->object_id) {
            $transaction = Transactions::where([
                        ['service_id', $this->request->service_id],
                        ['user_id', $this->request->user_id],
                        ['currency', $this->request->currency],
                        ['game_id', $this->additional->input('gameId')],
                        ['partner_id', $this->request->partner_id],
                        ['transaction_type', TransactionRequest::TRANS_BET],
                        ['status', TransactionRequest::STATUS_COMPLETED]
                    ])->first();
            if (!$transaction) {
                throw new ApiHttpException(404, null, CodeMapping::getByMeaning(StatusCode::BAD_ORDER));
            }
            $this->request->object_id = $transaction->object_id;
        }

        return $this->make($lastRecord);
    }

}
