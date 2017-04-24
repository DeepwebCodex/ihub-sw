<?php

namespace App\Components\Integrations\Vermantia\Tasks;

use iHubGrid\DynamicScheduler\Components\BasicTasks\RunArtisanCommand;

final class FetchEventsTask extends RunArtisanCommand
{
    protected $retries = 50;
    private $hours;

    public function __construct(int $hours)
    {
        $this->hours = $hours;

        parent::__construct('vermantia:fetch-events', [$this->hours], true);
    }
}