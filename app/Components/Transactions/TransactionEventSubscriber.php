<?php

namespace App\Components\Transactions;

use iHubGrid\QueueCommunicationProtocol\Implementation\Transaction\Publisher;
use iHubGrid\SeamlessWalletCore\Transactions\Events\AfterCompleteTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\AfterPendingTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\BeforeCompleteTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\BeforePendingTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\TransactionEventInterface;
use Illuminate\Events\Dispatcher;

class TransactionEventSubscriber
{
    public function onBeforePending(TransactionEventInterface $event)
    {
        
    }

    public function onBeforeComplected(TransactionEventInterface $event)
    {
        
    }

    public function onAfterPending(TransactionEventInterface $event)
    {
        (new Publisher())->dispatch($event);
    }

    public function onAfterComplected(TransactionEventInterface $event)
    {
        (new Publisher())->dispatch($event);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            BeforePendingTransactionEvent::class,
            'App\Components\Transactions\TransactionEventSubscriber@onBeforePending'
        );

        $events->listen(
            BeforeCompleteTransactionEvent::class,
            'App\Components\Transactions\TransactionEventSubscriber@onBeforeComplected'
        );

        $events->listen(
            AfterPendingTransactionEvent::class,
            'App\Components\Transactions\TransactionEventSubscriber@onAfterPending'
        );

        $events->listen(
            AfterCompleteTransactionEvent::class,
            'App\Components\Transactions\TransactionEventSubscriber@onAfterComplected'
        );
    }
}
