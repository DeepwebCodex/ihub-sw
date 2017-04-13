<?php

namespace App\Console\Commands\Vermantia;


use App\Components\Integrations\Vermantia\EventProcessor;
use App\Components\Integrations\Vermantia\Services\DataMapper;
use App\Components\Integrations\Vermantia\Tasks\NoMoreBetsTask;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use App\Models\Vermantia\EventLink;
use iHubGrid\DynamicScheduler\DynamicSchedulerService;

class ProcessEvent extends BaseEventCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vermantia:process-event {event-data : Event data for processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates given Vermantia events';

    protected $eventData;

    public function __construct()
    {
        parent::__construct('Vermantia', 'vermantia');
    }

    public function runHandle()
    {
        $this->eventData = json_decode($this->argument('event-data'), true);

        if($this->eventData) {

            $dataMap = new DataMapper($this->eventData, array_get($this->eventData, 'EventType'));

            if(EventLink::isExists($dataMap->getEventId())) {
                $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::DONE_DUPLICATE));
            }

            $eventProcessor = new EventProcessor();

            $created = $eventProcessor->create($dataMap);

            if(!$created) {
                $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::EVENT_NOT_FOUND));
            }

            (new DynamicSchedulerService())->addTask(
                new NoMoreBetsTask($dataMap->getEventId()),
                $dataMap->convertTimeToUtc('EventTime')->subSeconds(15)
            );

            $this->respondOk("Event with id: {$dataMap->getEventId()} ({$eventProcessor->getEventId()}) processed");

        }

        $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::MISS_ELEMENT));
    }
}
