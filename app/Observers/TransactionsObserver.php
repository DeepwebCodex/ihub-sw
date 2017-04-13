<?php

namespace App\Observers;

use App\Components\ExternalServices\Mysterion\TransactionProcessor;
use App\Models\Transactions;

/**
 * Class TransactionsObserver
 * @package App\Observers
 */
class TransactionsObserver
{
    /**
     * Listen to the Transactions created event.
     *
     * @param Transactions $transaction
     * @return void
     */
    public function saved(Transactions $transaction)
    {
        (new TransactionProcessor())->process($transaction);
    }
}
