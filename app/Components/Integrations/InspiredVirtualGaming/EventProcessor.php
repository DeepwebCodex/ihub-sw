<?php

namespace App\Components\Integrations\InspiredVirtualGaming;

use App\Components\Integrations\VirtualSports\Interfaces\EventProcessorInterface;
use App\Models\InspiredVirtualGaming\EventLink;

class EventProcessor extends \App\Components\Integrations\VirtualSports\EventProcessor implements EventProcessorInterface
{
    protected $eventId;

    public function __construct(int $eventId = null)
    {
        $this->eventBuilderClass = EventBuilder::class;
        $this->eventResultClass  = EventResult::class;

        $this->amqpExchange = config('integrations.inspired.amqp.exchange');
        $this->amqpKey = config('integrations.inspired.amqp.key');

        parent::__construct($eventId);
    }

    public static function getEvent(int $eventId) : EventProcessorInterface
    {
        $eventId = EventLink::getEventId($eventId);

        if($eventId == null)
        {
            throw new \RuntimeException("Event not found");
        }

        return new static($eventId);
    }
}