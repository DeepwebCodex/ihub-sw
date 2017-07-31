<?php

namespace App\Components\ExternalServices\FinanceCashflow;

use iHubGrid\SeamlessWalletCore\Transactions\Events\AfterCompleteTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\BeforePendingTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\TransactionEventInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

class FinanceService
{
    public function isEnabled() : bool
    {
        return (bool) config('finance.enabled');
    }

    public function dispatch(TransactionEventInterface $transactionEvent)
    {
        if(!$this->isEnabled()) {
            return false;
        }

        if(!$this->validateStatus($transactionEvent) || !$this->validateService($transactionEvent)) {
            return false;
        }

        dispatch((new SendFinanceJob($transactionEvent))->onConnection('finance_queue'));
    }

    protected function validateService(TransactionEventInterface $transaction)
    {
        return in_array(data_get($transaction->getTransactionRequest()->getAttributes(), 'service_id'), config('finance.services', []));
    }

    protected function validateStatus(TransactionEventInterface $transaction)
    {
        if($transaction instanceof BeforePendingTransactionEvent) {
            return true;
        }

        if($transaction instanceof AfterCompleteTransactionEvent && data_get($transaction->getTransactionRequest()->getAttributes(), 'transaction_type') !== TransactionRequest::TRANS_BET) {
            return true;
        }
    }
}
