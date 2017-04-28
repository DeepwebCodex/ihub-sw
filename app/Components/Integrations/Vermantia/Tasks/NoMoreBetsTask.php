<?php

namespace App\Components\Integrations\Vermantia\Tasks;

use iHubGrid\DynamicScheduler\Components\BasicTasks\RunArtisanCommand;

final class NoMoreBetsTask extends RunArtisanCommand
{
    protected $retries = 50;
    private $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;

        parent::__construct('vermantia:stop-bets', [$this->eventId], true);
    }
}