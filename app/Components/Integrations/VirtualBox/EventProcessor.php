<?php

namespace App\Components\Integrations\VirtualBox;


use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use App\Components\Integrations\VirtualSports\Interfaces\EventProcessorInterface;
use App\Exceptions\Api\ApiHttpException;
use App\Models\VirtualBoxing\EventLink;

class EventProcessor extends \App\Components\Integrations\VirtualSports\EventProcessor implements EventProcessorInterface
{
    protected $eventId;

    public function __construct(int $eventId = null)
    {
        $this->eventBuilderClass = EventBuilder::class;
        $this->eventResultClass  = EventResult::class;

        $this->amqpExchange = config('integrations.virtualBoxing.amqp.exchange');
        $this->amqpKey = config('integrations.virtualBoxing.amqp.key');

        parent::__construct($eventId);
    }

    public static function getEvent(int $eventId) : EventProcessorInterface
    {
        $eventId = EventLink::getEventId($eventId);

        if($eventId == null)
        {
            throw new ApiHttpException(200, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_FIND_EVENT));
        }

        return new static($eventId);
    }
}