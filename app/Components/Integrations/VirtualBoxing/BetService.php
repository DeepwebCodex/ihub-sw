<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\Category;
use App\Components\Integrations\VirtualSports\ConfigTrait;
use App\Components\Integrations\VirtualSports\Event;
use App\Components\Integrations\VirtualSports\EventParticipant;
use App\Components\Integrations\VirtualSports\Tournament;
use App\Components\Integrations\VirtualSports\Translate;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Line\Sportform;

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
     * ResultService constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $match
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    public function setBet(array $match):void
    {
        $scheduleId = (int)$match['scheduleId'];
        $this->eventVbId = $scheduleId;

        if (EventLink::checkExistsByVbId($scheduleId)) {
            throw new ApiHttpException(200, 'done_duplicate');
            //return $this->respondSuccess($this->getMessageByCode('done_duplicate'));
            /*return $this->Func->success($this->lang->line('done_duplicate') . ' Duplicate-> '
                . print_r($duplicate, true) . ' obj-> ' . print_r(Input::all(), true));*/
        }

        $sportId = $this->getConfigOption('sport_id');
        $sportForm = Sportform::findById($sportId);

        foreach ($sportForm as $item) {
            $itemId = $item['id'];
            if ($item['is_alive']) {
                $liveSportFormId = $itemId;
            } else {
                $prebetSportForm = $itemId;
            }
        }
        if (!isset($liveSportFormId, $prebetSportForm)) {
            throw new ApiHttpException(400, "Can't find sportform");
        }

        \DB::connection('line')->transaction(function () use ($scheduleId, $sportId, $match, $sportForm) {
            $category = new Category();
            $category->create(
                $match['competition'],
                $sportId,
                $this->getConfigOption('country_id')
            );

            $tournament = new Tournament($this->config);
            $tournament->create(
                $match['location'],
                $category->getCategoryId(),
                $sportForm->prebet_id,
                $sportForm->live_id
            );

            $event = new Event($this->config);
            $event->create($match['date'], $match['time'], $match['name'], $tournament->getTournamentId());
            $eventId = $event->getEventId();
            $this->eventId = $eventId;

            $eventParticipantHome = new EventParticipant($this->config);
            $eventParticipantHome->create(1, $eventId, $match['home']);
            $eventParticipantAway = new EventParticipant($this->config);
            $eventParticipantAway->create(2, $eventId, $match['away']);

            (new ResultService($this->config))->initResultEvent($eventId);

            $market = new Market(
                $this->config,
                $eventParticipantHome->getEventParticipantId(),
                $eventParticipantAway->getEventParticipantId()
            );
            $market->setMarkets($match['bet'], $eventId);

            Translate::save();

            (new EventLink())->create($scheduleId, $eventId);
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
}
