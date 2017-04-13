<?php

namespace App\Console\Commands\Vermantia;


use App\Components\Integrations\Vermantia\EventProcessor;
use App\Components\Integrations\Vermantia\Services\DataMapper;
use App\Components\Integrations\Vermantia\Tasks\CancelEventTask;
use App\Components\Integrations\Vermantia\Tasks\FinishEventTask;
use App\Components\Integrations\Vermantia\VermantiaDirectory;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use App\Console\Commands\Vermantia\Traits\DateParserTrait;
use Carbon\Carbon;
use iHubGrid\DynamicScheduler\DynamicSchedulerService;

class ResultEvent extends BaseEventCommand
{
    use DateParserTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vermantia:process-results {event-id : Event id for resulting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolves resulting for Vermantia events';

    protected $eventId;

    public function __construct()
    {
        parent::__construct('Vermantia', 'vermantia');
    }

    public function runHandle()
    {
        $this->eventId = $this->argument('event-id');

        if($this->eventId) {

            $data = app('VermantiaGameService')->getResult($this->eventId, 300);

            $dataEvent = $this->filterEventData($data, Carbon::now('UTC'));

            if($dataEvent) {
                $dataMap = new DataMapper($dataEvent, array_get($dataEvent, 'EventType'));

                $processor = EventProcessor::getEvent($dataMap->getEventId());

                $processor->setResult($dataMap, false);

                (new DynamicSchedulerService())->addTask(
                    new FinishEventTask($dataMap->getEventId()),
                    Carbon::now()
                );

                $this->respondOk("Event with id: {$dataMap->getEventId()} ({$processor->getEventId()}) results resolved");
            } else {

                (new DynamicSchedulerService())->addTask(
                    new CancelEventTask($this->eventId),
                    Carbon::now()
                );

                $this->respondOk("Event with id: {$this->eventId} results where too late for processing");
            }
        }

        $this->respondOk(CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::MISS_ELEMENT));
    }

    protected function filterEventData(array $rawData, Carbon $timeAfterRequest) : array
    {
        return collect($rawData)->filter(function($value, $key) {
            return in_array($key, VermantiaDirectory::eventNodesList());
        })->transform(function($item) use($rawData, $timeAfterRequest) {
            $item['dateDiff'] = $this->getTimeDiff($rawData['LocalTime'], $rawData['UtcTime'], $timeAfterRequest);
            return $item;
        })->first();
    }
}
