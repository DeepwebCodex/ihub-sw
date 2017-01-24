<?php

namespace App\Components\Integrations\VirtualBox;

use App\Components\Integrations\VirtualBox\Services\DataMapper;
use App\Components\Integrations\VirtualSports\Interfaces\EventResultInterface;

class EventResult extends \App\Components\Integrations\VirtualSports\EventResult implements EventResultInterface
{
    public function __construct(array $data, int $eventId)
    {
        $this->config = config('integrations.virtualBoxing');

        $this->eventType = 'box';

        $this->requestData = $data;

        $this->eventData = array_get($this->requestData, 'result', []);

        parent::__construct($data, $eventId, DataMapper::class);
    }
}