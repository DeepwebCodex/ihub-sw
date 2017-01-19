<?php

namespace App\Components\Integrations\InspiredVirtualGaming;

use App\Components\Integrations\InspiredVirtualGaming\Modules\CategoryService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\DataMapper;
use App\Components\Integrations\InspiredVirtualGaming\Modules\EventService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\OutcomeService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\TournamentService;
use App\Components\Integrations\VirtualSports\Result;
use App\Components\Traits\ConfigTrait;
use App\Models\Line\Category;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use App\Models\Line\OutcomeType;
use App\Models\Line\StatusDesc;
use App\Models\Line\Tournament;
use Illuminate\Database\Eloquent\Collection;

class EventBuilder
{
    use ConfigTrait;

    protected $eventData;
    protected $eventType;
    protected $controllerId;

    protected $eventParticipants;

    public function __construct(array $eventData)
    {
        $this->config = config('integrations.inspired');

        $this->eventData = $eventData;

        $this->eventType = (int) array_get($this->eventData, 'EventType');
        $this->controllerId = (int) array_get($this->eventData, 'ControllerId');
    }

    public function create()
    {
        $dataMap = new DataMapper($this->eventData, (int) array_get($this->eventData, 'EventType'));

        //need a category for event
        $eventCategory = $this->getCategory();

        //creating tournament for category
        $tournament = $this->getTournament($eventCategory->id, $dataMap->getEventName());

        //creating event for this tournament
        $event = $this->getEvent(
            $tournament->id,
            $dataMap->getEventTime(),
            $tournament->name,
            $dataMap->getEventId(),
            $dataMap->getParticipants()
        );

        //initializing results for this event with zero data
        (new Result())->initResultEvent("prebet", $event->id);

        //Get all market templates for sport
        $eventMarketConfig = $this->getConfigOption('sports.'. $this->eventType .'.markets');
        $marketTemplatesIds = array_unique(array_flatten($eventMarketConfig));

        $eventMarketList = array_keys($eventMarketConfig);

        $marketTemplates = MarketTemplate::getMarketTemplates($marketTemplatesIds);

        if(!$marketTemplates) {
            throw new \RuntimeException("There is no market templates for event type {$this->eventType}");
        }

        $mappedMarketsWithOutcomes = $dataMap->getMarketsWithOutcomes();

        foreach ($mappedMarketsWithOutcomes as $market => $outcomes) {

            if(in_array($market, $eventMarketList)) {

                $mappedMarketIds = array_get($eventMarketConfig, $market);

                $currentMarketTemplates = $marketTemplates->whereIn('id', $mappedMarketIds);

                foreach ($currentMarketTemplates as $currentMarketTemplate) {
                    /**@var MarketTemplate $currentMarketTemplate*/

                    //getOutcomeTypes for this market template

                    $outcomeTypes = OutcomeType::getOutcomeTypes($currentMarketTemplate->outcome_types);

                    if(!$outcomeTypes) {
                        throw new \RuntimeException("There is no outcome types for market {$market} with template id: {$currentMarketTemplate->id}");
                    }

                    $marketModel = Market::create([
                        'event_id'              => $event->id,
                        'market_template_id'    => $currentMarketTemplate->id,
                        'result_type_id'        => $this->getConfigOption('market.result_type_id'),
                        'max_bet'               => null,
                        'max_payout'            => null,
                        'stop_loss'             => null,
                        'weight'                => $currentMarketTemplate->weigh,
                        'service_id'            => $this->getConfigOption('service_id'),
                        'user_id'               => $this->getConfigOption('user_id')
                    ]);

                    if(! $marketModel) {
                        throw new \RuntimeException("Unable to create market for template {$currentMarketTemplate->id}");
                    }

                    foreach ($outcomes as $outcome) {
                        $this->getOutcome($market, $outcome, $mappedMarketsWithOutcomes, $currentMarketTemplate, $outcomeTypes, $marketModel, $this->eventParticipants);
                    }
                }
            }
        }

        //by default market is created as suspended=yes so to resume setting it to no
        if(!(new Market())->resumeMarketEvent($event->id))
        {
            throw new \RuntimeException("Unable to resume market event {$event->id}");
        }

        //creating new status description for this event
        if(! StatusDesc::create([
            'status_type' => StatusDesc::STATUS_NOT_STARTED,
            'name' => StatusDesc::STATUS_NOT_STARTED,
            'event_id' => $event->id
        ])){
            throw new \RuntimeException("Can't insert status_desc");
        }

    }

    private function getOutcome(string $market, array $outcome, array $mappedMarketsWithOutcomes,MarketTemplate $marketTemplate, Collection $outcomeTypes, Market $marketModel, Collection $eventParticipants) : Outcome
    {
        return (new OutcomeService(
            $market,
            $outcome,
            $mappedMarketsWithOutcomes,
            $marketTemplate,
            $outcomeTypes,
            $marketModel,
            $eventParticipants
        ))->resolve();
    }

    private function getEvent(int $tournamentId, string $eventTime, string $eventName, $originalEventId, array $participants) : Event
    {
        $eventService = (new EventService(
            $tournamentId,
            $eventTime,
            $eventName,
            100,
            $this->getConfigOption('sports.'. $this->eventType . '.max_bet'),
            $this->getConfigOption('sports.'. $this->eventType . '.max_payout'),
            $this->getConfigOption('sports.'. $this->eventType . '.stop_loss'),
            $this->getConfigOption('country_id'),
            $this->getConfigOption('sport_id'),
            $this->getConfigOption('user_id'),
            $originalEventId,
            $participants
        ));

        $event = $eventService->resolve();

        $this->eventParticipants = $eventService->getEventParticipants();

        return $event;
    }

    private function getTournament(int $categoryId, string $eventName) : Tournament
    {
        return (new TournamentService(
            $eventName,
            $categoryId,
            100,
            (int) $this->getConfigOption('sports.'. $this->eventType . '.sportform_prebet_id'),
            (int) $this->getConfigOption('country_id'),
            $this->getConfigOption('sports.'. $this->eventType . '.max_bet'),
            $this->getConfigOption('sports.'. $this->eventType . '.max_payout'),
            $this->getConfigOption('sports.'. $this->eventType . '.stop_loss'),
            $this->getConfigOption('sports.'. $this->eventType . '.sportform_live_id'),
            $this->getConfigOption('gender'),
            (int) $this->getConfigOption('user_id')
        ))->resolve();
    }

    private function getCategory() : Category
    {
        $eventName = $this->getConfigOption('sports.'. $this->eventType . '.name');

        $category = (new CategoryService(
            "{$eventName}_{$this->controllerId}",
            $this->getConfigOption('sport_id'),
            100,
            $this->getConfigOption('gender'),
            (int) $this->getConfigOption('country_id')
        ))->resolve();

        return $category;
    }
}