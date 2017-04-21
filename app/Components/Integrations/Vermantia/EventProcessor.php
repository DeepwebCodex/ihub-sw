<?php

namespace App\Components\Integrations\Vermantia;

use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use App\Components\Integrations\VirtualSports\Interfaces\EventProcessorInterface;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Models\Vermantia\EventLink;

class EventProcessor extends \App\Components\Integrations\VirtualSports\EventProcessor implements EventProcessorInterface
{
    protected $eventId;

    public function __construct(int $eventId = null)
    {
        $this->eventBuilderClass = EventBuilder::class;
        $this->eventResultClass  = EventResult::class;

        $this->amqpExchange = config('integrations.vermantia.amqp.exchange');
        $this->amqpKey = config('integrations.vermantia.amqp.key');

        parent::__construct($eventId);
    }

    public static function getEvent(int $eventId) : EventProcessorInterface
    {
        $eventId = EventLink::getEventId($eventId);

        if($eventId == null)
        {
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::EVENT_MODEL_NOT_FOUND));
        }

        return new static($eventId);
    }
}