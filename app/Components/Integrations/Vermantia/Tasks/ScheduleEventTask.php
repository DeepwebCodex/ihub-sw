<?php

namespace App\Components\Integrations\Vermantia\Tasks;

use iHubGrid\DynamicScheduler\Components\BasicTasks\RunArtisanCommand;

final class ScheduleEventTask extends RunArtisanCommand
{
    protected $retries = 50;
    private $eventData;

    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;

        parent::__construct('vermantia:process-event', [json_encode($this->eventData)], true);
    }
}