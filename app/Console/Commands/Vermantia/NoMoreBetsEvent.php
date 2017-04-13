<?php

namespace App\Console\Commands\Vermantia;


use App\Components\Integrations\Vermantia\EventProcessor;
use App\Components\Integrations\Vermantia\Tasks\ResultEventTask;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use Carbon\Carbon;
use iHubGrid\DynamicScheduler\DynamicSchedulerService;

class NoMoreBetsEvent extends BaseEventCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vermantia:stop-bets {event-id : Id of an event for signal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Closes all markets from bets for Vermantia events';

    protected $eventId;

    public function __construct()
    {
        parent::__construct('Vermantia', 'vermantia');
    }

    public function runHandle()
    {
        $this->eventId = (int) $this->argument('event-id');

        if($this->eventId) {

            $processor = EventProcessor::getEvent($this->eventId);

            $processor->stopBets();

            (new DynamicSchedulerService())->addTask(
                new ResultEventTask($this->eventId),
                Carbon::now()
            );

            $this->respondOk("Bets was stopped for event: {$this->eventId} ({$processor->getEventId()})");
        }

        $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::MISS_ELEMENT));
    }
}
