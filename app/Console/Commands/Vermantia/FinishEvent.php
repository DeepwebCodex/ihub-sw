<?php

namespace App\Console\Commands\Vermantia;

use App\Components\Integrations\Vermantia\EventProcessor;
use App\Components\Integrations\Vermantia\Tasks\FinishEventTask;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use Carbon\Carbon;
use Exception;
use iHubGrid\DynamicScheduler\DynamicSchedulerService;

class FinishEvent extends BaseEventCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vermantia:finish-event {event-id : Id of an event for signal} {attempt=0 : how many times this task was attempted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finishes Vermantia event';

    protected $eventId;

    public function __construct()
    {
        parent::__construct('Vermantia', 'vermantia');
    }

    public function runHandle()
    {
        $this->attempt = $this->argument('attempt') ?? 0;
        $this->eventId = (int) $this->argument('event-id');

        if($this->eventId) {

            $processor = EventProcessor::getEvent((int) $this->eventId);

            $processor->finish();

            $this->respondOk("Event: {$this->eventId} ({$processor->getEventId()}) was finished");
        }

        $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::MISS_ELEMENT));
    }

    protected function failing(Exception $e, int $attempt = 0)
    {
        if($attempt < $this->retryAttempts) {
            $attempt = $attempt +1;
            (new DynamicSchedulerService())->addTask(new FinishEventTask($this->eventId), Carbon::now()->addSeconds($this->retryDelay), $attempt);
        }
    }
}
