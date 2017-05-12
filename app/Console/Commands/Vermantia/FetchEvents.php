<?php

namespace App\Console\Commands\Vermantia;


use App\Components\Integrations\Vermantia\EventProcessor;
use App\Components\Integrations\Vermantia\Services\DataMapper;
use App\Components\Integrations\Vermantia\Tasks\FetchEventsTask;
use App\Components\Integrations\Vermantia\Tasks\ScheduleEventTask;
use App\Components\Integrations\Vermantia\VermantiaDirectory;
use App\Console\Commands\Vermantia\Traits\DateParserTrait;
use Carbon\Carbon;
use Exception;
use iHubGrid\DynamicScheduler\DynamicSchedulerService;
use Illuminate\Support\Collection;

class FetchEvents extends BaseEventCommand
{
    use DateParserTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vermantia:fetch-events {hours=1 : Event fetch time range} {attempt=0 : how many times this task was attempted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and schedules Vermantia events for processing';

    protected $hours = 1;

    public function __construct()
    {
        parent::__construct('Vermantia', 'vermantia');
    }

    public function runHandle()
    {
        $this->attempt = $this->argument('attempt') ?? 0;
        $this->hours = (int)$this->argument('hours');

        /*
         *  Self run every 1 hour TODO::remove after full featured Scheduler is completed
         */
        //(new DynamicSchedulerService())->addTask(new FetchEventsTask($this->hours), Carbon::now()->addHours(1));

        $eventData = app('VermantiaGameService')->getUpcomingEvents($this->hours);

        $dataEvents = $this->filterEventData($eventData, Carbon::now('UTC'));

        $eventsAdded = 0;

        if($dataEvents->isNotEmpty()) {
            $dataEvents->each(function($item) use(&$eventsAdded){

                $eventType = array_get($item, 'EventType');

                if(!in_array($eventType, VermantiaDirectory::getEnabledSports())) {
                    $this->respond("Event type of {$eventType} currently is not supported");
                    return;
                }

                /**Pre create new Tournaments and Categories for fetched events*/

                $dataMap = new DataMapper($item, $eventType);

                (new EventProcessor())->createOnlyTournamentAndCategory($dataMap);

                (new DynamicSchedulerService())->addTask(new ScheduleEventTask($item), Carbon::now());

                $eventsAdded++;
            });
        } else {
            $this->respondOk("No new events for: " . Carbon::now()->format('Y-m-d H:i:s'));
        }

        $this->respondOk("Events added: {$eventsAdded}");
    }

    protected function filterEventData(array $rawData, Carbon $timeAfterRequest) : Collection
    {
        return collect($rawData)->filter(function($value, $key) {
            return in_array($key, VermantiaDirectory::eventNodesList());
        })->flatten(1)->transform(function($item) use($rawData, $timeAfterRequest) {
            $item['dateDiff'] = $this->getTimeDiff($rawData['LocalTime'], $rawData['UtcTime'], $timeAfterRequest);
            return $item;
        });
    }

    protected function failing(Exception $e, int $attempt = 0)
    {
        if($attempt < $this->retryAttempts) {
            $attempt = $attempt +1;
            (new DynamicSchedulerService())->addTask(new FetchEventsTask($this->hours), Carbon::now()->addSeconds($this->retryDelay), $attempt);
        }
    }
}
