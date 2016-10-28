<?php

namespace App\Components\Integrations\VirtualSports;

use App\Exceptions\Api\ApiHttpException;
use App\Models\Line\Event as EventModel;

/**
 * Class Event
 * @package App\Components\Integrations\VirtualSports
 */
class Event
{
    use ConfigTrait;

    /**
     * @var EventModel
     */
    protected $eventModel;

    /**
     * Event constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param $matchDate
     * @param $matchTime
     * @param $matchName
     * @param $tournamentId
     * @return void
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function create($matchDate, $matchTime, $matchName, $tournamentId):void
    {
        Translate::add($matchName);

        $eventModel = new EventModel([
            'tournament_id' => $tournamentId,
            'dt' => $matchDate . ' ' . $matchTime,
            'name' => $matchName,
            'locked' => $this->getConfigOption('locked'),
            'weigh' => $this->getConfigOption('weigh'),
            'del' => $this->getConfigOption('del'),
            'max_bet' => $this->getConfigOption('max_bet'),
            'max_payout' => $this->getConfigOption('max_payout'),
            'margin' => $this->getConfigOption('margin'),
            'margin_prebet' => $this->getConfigOption('margin_prebet'),
            'stop_loss' => $this->getConfigOption('weigh'),
            'user_id' => $this->getConfigOption('user_id'),
        ]);
        if (!$eventModel->save()) {
            throw new ApiHttpException(400, "Can't insert event");
        }
        $this->eventModel = $eventModel;
    }

    /**
     * @return int
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function getEventId():int
    {
        if (!$this->eventModel) {
            throw new ApiHttpException(400, "Can't insert event");
        }
        return (int)$this->eventModel->id;
    }
}
