<?php

namespace App\Components\Integrations\InspiredVirtualGaming;


use App\Components\Integrations\InspiredVirtualGaming\Services\DataMapper;
use App\Components\Integrations\VirtualSports\Interfaces\EventResultInterface;


class EventResult extends \App\Components\Integrations\VirtualSports\EventResult implements EventResultInterface
{
    public function __construct(array $data, int $eventId)
    {
        $this->config = config('integrations.inspired');

        $this->requestData = $data;

        $this->eventData = array_get($this->requestData, 'event', []);

        $this->eventType = (int) array_get($this->eventData, 'EventType');

        parent::__construct($data, $eventId, DataMapper::class);
    }
}