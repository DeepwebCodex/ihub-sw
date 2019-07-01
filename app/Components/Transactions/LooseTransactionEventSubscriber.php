<?php

namespace App\Components\Transactions;

use iHubGrid\QueueCommunicationProtocol\Implementation\LooseTransaction\LoosePublisher;
use iHubGrid\SeamlessWalletCore\Transactions\Events\LooseTransactionEvent;
use Illuminate\Events\Dispatcher;

class LooseTransactionEventSubscriber
{
    public function onAfterComplected(LooseTransactionEvent $event): void
    {
        (new LoosePublisher())->dispatch($event->getPacket());
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            LooseTransactionEvent::class,
            'App\Components\Transactions\LooseTransactionEventSubscriber@onAfterComplected'
        );
    }
}
