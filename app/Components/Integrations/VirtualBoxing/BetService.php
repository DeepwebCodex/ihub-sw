<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\Category;
use App\Components\Integrations\VirtualSports\Result;
use App\Components\Traits\ConfigTrait;
use App\Components\Integrations\VirtualSports\Event;
use App\Components\Integrations\VirtualSports\EventParticipant;
use App\Components\Integrations\VirtualSports\Tournament;
use App\Components\Integrations\VirtualSports\Translate;
use App\Exceptions\Api\VirtualBoxing\DuplicateException;
use App\Exceptions\Api\VirtualBoxing\ErrorException;
use App\Models\Line\Sportform;
use App\Models\VirtualBoxing\EventLink;

/**
 * Class BetService
 * @package App\Components\Integrations\VirtualBoxing
 */
class BetService
{
    use ConfigTrait;

    /**
     * @var int
     */
    protected $eventId;

    /**
     * @var int
     */
    protected $eventVbId;

    /**
     * BetService constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $match
     * @return void
     * @throws \RuntimeException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \InvalidArgumentException
     * @throws \App\Exceptions\Api\VirtualBoxing\DuplicateException
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \Exception
     */
    public function setBet(array $match)
    {
        $this->eventVbId = (int)$match['scheduleId'];

        $this->checkDuplicate($this->eventVbId);

        $sportId = $this->getConfigOption('sport_id');

        $sportFormIds = $this->getSportFormIds($sportId);

        \DB::connection('line')->transaction(function () use ($sportId, $match, $sportFormIds) {
            $category = new Category();
            $categoryCreateResult = $category->create(
                $match['competition'],
                $sportId,
                $this->getConfigOption('country_id')
            );
            if (!$categoryCreateResult) {
                throw new ErrorException('error_create_category');
            }

            $tournament = new Tournament($this->config);
            $tournamentCreateResult = $tournament->create(
                $match['location'],
                $category->getCategoryId(),
                $sportFormIds['prebet'],
                $sportFormIds['live']
            );
            if (!$tournamentCreateResult) {
                throw new ErrorException('error_create_tournament');
            }

            $event = new Event($this->config);
            $eventCreateResult = $event->create(
                $match['date'],
                $match['time'],
                $match['name'],
                $tournament->getTournamentId()
            );
            if (!$eventCreateResult) {
                throw new ErrorException('Can\'t insert event');
            }

            $eventId = $event->getEventId();
            $this->eventId = $eventId;

            $eventParticipantHome = $this->createEventParticipant(1, $this->eventId, $match['home']);
            $eventParticipantAway = $this->createEventParticipant(2, $this->eventId, $match['away']);

            $eventType = $this->getConfigOption('event_type');
            (new Result())->initResultEvent($eventType, $eventId);

            $market = new Market(
                $this->config,
                $eventParticipantHome->getEventParticipantId(),
                $eventParticipantAway->getEventParticipantId()
            );

            $matchBets = is_numeric(key($match['bet'])) ? $match['bet'] : [$match['bet']];
            $market->setMarkets($matchBets, $eventId);

            Translate::save();

            $eventLinkModel = new EventLink([
                'event_vb_id' => (string)$this->eventVbId,
                'event_id' => $eventId
            ]);
            if (!$eventLinkModel->save()) {
                throw new ErrorException('cant_insert_link');
            }
        });
    }

    /**
     * @return int
     */
    public function getEventId():int
    {
        return $this->eventId;
    }

    /**
     * @return int
     */
    public function getEventVbId():int
    {
        return $this->eventVbId;
    }

    /**
     * @param $eventVbId
     * @throws \App\Exceptions\Api\VirtualBoxing\DuplicateException
     */
    protected function checkDuplicate(int $eventVbId)
    {
        if (EventLink::getByVbId($eventVbId)) {
            throw new DuplicateException(compact($eventVbId));
        }
    }

    /**
     * @param $sportId
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function getSportFormIds(int $sportId):array
    {
        $sportForm = Sportform::findById($sportId);
        foreach ($sportForm as $item) {
            $itemId = $item['id'];
            if ($item['is_live']) {
                $liveSportFormId = $itemId;
            } else {
                $preBetSportFormId = $itemId;
            }
        }
        if (!isset($liveSportFormId, $preBetSportFormId)) {
            throw new ErrorException("Can't find sportform");
        }
        return [
            'live' => $liveSportFormId,
            'prebet' => $preBetSportFormId
        ];
    }

    /**
     * @param int $number
     * @param int $eventId
     * @param string $participantName
     * @return EventParticipant
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function createEventParticipant(int $number, int $eventId, string $participantName):EventParticipant
    {
        $eventParticipant = new EventParticipant($this->config);
        if (!$eventParticipant->create($number, $eventId, $participantName)) {
            throw new ErrorException('error_create_participant');
        }
        return $eventParticipant;
    }
}
