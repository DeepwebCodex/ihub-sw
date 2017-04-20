<?php

namespace App\Components\Integrations\VirtualBox;

use App\Components\Integrations\VirtualBox\Services\OutcomeService;
use App\Components\Integrations\VirtualSports\CodeMappingVirtualSports;
use App\Components\Integrations\VirtualSports\Interfaces\DataMapperInterface;
use App\Components\Integrations\VirtualSports\Interfaces\EventBuilderInterface;
use App\Components\Integrations\VirtualSports\Services\CategoryService;
use App\Components\Integrations\VirtualSports\Services\EventService;
use App\Components\Integrations\VirtualSports\Services\TournamentService;
use App\Components\Traits\ConfigTrait;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Models\Line\Category;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use App\Models\Line\Sportform;
use App\Models\Line\Tournament;
use App\Models\VirtualBoxing\EventLink;
use Illuminate\Database\Eloquent\Collection;

class EventBuilder extends \App\Components\Integrations\VirtualSports\EventBuilder implements EventBuilderInterface
{
    use ConfigTrait;

    public function __construct(DataMapperInterface $dataMapper)
    {
        $this->config = config('integrations.virtualBoxing');

        $this->eventType = $dataMapper->getEventType();

        parent::__construct($dataMapper);
    }

    protected function getCategory() : Category
    {
        $eventName = array_get($this->dataMapper->getRawData(), 'match.competition');

        $category = (new CategoryService(
            $eventName,
            $this->getConfigOption('sport_id'),
            100,
            $this->getConfigOption('gender'),
            (int) $this->getConfigOption('country_id')
        ))->resolve();

        return $category;
    }

    protected function getTournament(int $categoryId, string $eventName) : Tournament
    {
        list($live, $preBet) = array_values(Sportform::getSportFormIds($this->getConfigOption('sport_id')));

        return (new TournamentService(
            $eventName,
            $categoryId,
            100,
            (int) $preBet,
            (int) $this->getConfigOption('country_id'),
            $this->getConfigOption('max_bet'),
            $this->getConfigOption('max_payout'),
            $this->getConfigOption('stop_loss'),
            (int) $live,
            $this->getConfigOption('gender'),
            (int) $this->getConfigOption('user_id')
        ))->resolve();
    }

    protected function getEvent(int $tournamentId, string $eventTime, string $eventName, $originalEventId, array $participants) : Event
    {
        $eventService = (new EventService(
            $tournamentId,
            $eventTime,
            $eventName,
            100,
            $this->getConfigOption('max_bet'),
            $this->getConfigOption('max_payout'),
            $this->getConfigOption('stop_loss'),
            $this->getConfigOption('country_id'),
            $this->getConfigOption('sport_id'),
            $this->getConfigOption('user_id'),
            $participants
        ));

        $event = $eventService->resolve();

        if(! EventLink::create([
            'event_id' => $event->id,
            'event_vb_id' => (int) $originalEventId
        ])) {
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_CREATE_LINK));
        }

        $this->eventParticipants = $eventService->getEventParticipants();

        return $event;
    }

    protected function getOutcome(string $market, array $outcome, array $mappedMarketsWithOutcomes, MarketTemplate $marketTemplate, Collection $outcomeTypes, Market $marketModel, Collection $eventParticipants) : Outcome
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
}