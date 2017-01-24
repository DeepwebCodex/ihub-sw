<?php

namespace App\Components\Integrations\VirtualSports;

use App\Components\Integrations\VirtualSports\Interfaces\DataMapperInterface;
use App\Components\Traits\ConfigTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Line\Category;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use App\Models\Line\OutcomeType;
use App\Models\Line\StatusDesc;
use App\Models\Line\Tournament;
use Illuminate\Database\Eloquent\Collection;

abstract class EventBuilder
{
    use ConfigTrait;

    protected $eventType;

    protected $eventParticipants;

    protected $dataMapper;

    public function __construct(DataMapperInterface $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    public function create()
    {
        //need a category for event
        $eventCategory = $this->getCategory();

        //creating tournament for category
        $tournament = $this->getTournament($eventCategory->id, $this->dataMapper->getEventName());

        //creating event for this tournament
        $event = $this->getEvent(
            $tournament->id,
            $this->dataMapper->getEventTime(),
            $tournament->name,
            $this->dataMapper->getEventId(),
            $this->dataMapper->getParticipants()
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

        $mappedMarketsWithOutcomes = $this->dataMapper->getMarketsWithOutcomes();

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

                    //Group markets by result types
                    $marketResultTypes = array_unique(array_pluck($outcomes, 'ResultTypeId'));

                    foreach ($marketResultTypes as $resultType) {

                        //get only outcomes that belong only to current result type
                        $groupedOutcomes = array_where($outcomes, function ($outcome) use ($resultType) {
                            return array_get($outcome, 'ResultTypeId') === $resultType;
                        });

                        $marketModel = Market::create([
                            'event_id' => $event->id,
                            'market_template_id' => $currentMarketTemplate->id,
                            'result_type_id' => $resultType !== null ? $resultType : $this->getConfigOption('market.result_type_id'),
                            'max_bet' => $this->getConfigOption('sports.'. $this->eventType .'.max_bet'),
                            'max_payout' => $this->getConfigOption('sports.'. $this->eventType .'.max_payout'),
                            'stop_loss' => $this->getConfigOption('sports.'. $this->eventType .'.stop_loss'),
                            'weight' => $currentMarketTemplate->weigh,
                            'service_id' => $this->getConfigOption('service_id'),
                            'user_id' => $this->getConfigOption('user_id')
                        ]);

                        if (!$marketModel) {
                            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_CREATE_MARKET));
                        }

                        foreach ($groupedOutcomes as $outcome) {
                            $this->getOutcome($market, $outcome, $mappedMarketsWithOutcomes, $currentMarketTemplate, $outcomeTypes, $marketModel, $this->eventParticipants);
                        }
                    }
                }
            }
        }

        //by default market is created as suspended=yes so to resume setting it to no
        if(!(new Market())->resumeMarketEvent($event->id))
        {
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_UPDATE_MARKET));
        }

        //creating new status description for this event
        if(! StatusDesc::create([
            'status_type' => StatusDesc::STATUS_NOT_STARTED,
            'name' => StatusDesc::STATUS_NOT_STARTED,
            'event_id' => $event->id
        ])){
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_UPDATE_EVENT_STATUS));
        }

        return $event->id;
    }

    abstract protected function getOutcome(string $market, array $outcome, array $mappedMarketsWithOutcomes, MarketTemplate $marketTemplate, Collection $outcomeTypes, Market $marketModel, Collection $eventParticipants) : Outcome;

    abstract protected function getEvent(int $tournamentId, string $eventTime, string $eventName, $originalEventId, array $participants) : Event;

    abstract protected function getTournament(int $categoryId, string $eventName) : Tournament;

    abstract protected function getCategory() : Category;
}