<?php

namespace App\Components\Integrations\InspiredVirtualGaming;

use App\Components\Integrations\VirtualSports\Interfaces\DataMapperInterface;
use App\Components\Integrations\VirtualSports\Interfaces\EventResultInterface;


class EventResult extends \App\Components\Integrations\VirtualSports\EventResult implements EventResultInterface
{
    public function __construct(DataMapperInterface $dataMapper, int $eventId)
    {
        $this->config = config('integrations.inspired');

        $this->eventType = $dataMapper->getEventType();

        parent::__construct($eventId, $dataMapper);
    }
}