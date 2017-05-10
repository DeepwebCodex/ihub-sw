<?php

namespace App\Components\Integrations\Vermantia\Tasks;

use iHubGrid\DynamicScheduler\Components\BasicTasks\RunArtisanCommand;

final class CancelEventTask extends RunArtisanCommand
{
    protected $retries = 50;
    private $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;

        parent::__construct('vermantia:cancel-event', [$this->eventId], true);
    }
}