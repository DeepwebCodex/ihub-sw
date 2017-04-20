<?php

namespace App\Components\ExternalServices\Mysterion;

use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Spatie\Fractal\ArraySerializer;

/**
 * Class TransactionsObserver
 * @package App\Observers
 */
class TransactionProcessor
{
    /**
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return (bool)config('external.api.mysterion.is_enabled');
    }

    /**
     * @param Transactions $transaction
     * @return bool
     */
    protected function validateTransactionStatus(Transactions $transaction): bool
    {
        return $transaction->getAttributeValue('status') === TransactionRequest::STATUS_COMPLETED;
    }

    /**
     * Listen to the Transactions created event.
     *
     * @param Transactions $transaction
     * @return void
     */
    public function process(Transactions $transaction)
    {
        if (!$this->isEnabled() || !$this->validateTransactionStatus($transaction)) {
            return;
        }
        $transactionInfo = fractal()
            ->item($transaction, new TransactionTransformer())
            ->serializeWith(new ArraySerializer())
            ->toArray();

        $transactionInfo = \json_encode($transactionInfo);
        $job = (new SendTransactionJob($transactionInfo))->onConnection('mysterion_transactions');
        dispatch($job);
    }
}
