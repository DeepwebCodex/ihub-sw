<?php

namespace App\Components\ExternalServices\FinanceCashflow;

use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

class FinanceService
{
    public function isEnabled() : bool
    {
        return (bool) config('finance.enabled');
    }

    public function dispatch(Transactions $transaction)
    {
        if(!$this->isEnabled()) {
            return false;
        }

        if(!$this->validateStatus($transaction) || !$this->validateService($transaction)) {
            return false;
        }

        dispatch((new SendFinanceJob($transaction))->onConnection('finance_queue'));
    }

    protected function validateService(Transactions $transaction)
    {
        return in_array($transaction->getAttributeValue('service_id'), config('finance.services', []));
    }

    protected function validateStatus(Transactions $transaction)
    {
        return $transaction->getAttributeValue('status') === TransactionRequest::STATUS_COMPLETED;
    }
}
