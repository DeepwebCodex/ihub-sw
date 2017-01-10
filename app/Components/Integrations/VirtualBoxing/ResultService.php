<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Traits\ConfigTrait;
use App\Exceptions\Api\VirtualBoxing\DuplicateException;
use App\Exceptions\Api\VirtualBoxing\ErrorException;
use App\Models\Line\ResultGame;
use App\Models\Line\ResultGameTotal;
use App\Models\Line\Sport;
use App\Models\VirtualBoxing\EventLink;
use App\Models\VirtualBoxing\Result;

/**
 * Class ResultService
 * @package App\Components\Integrations\VirtualBoxing
 */
class ResultService
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
     * @param array $config
     * @param int $eventVbId
     */
    public function __construct(array $config, int $eventVbId)
    {
        $this->config = $config;

        $event = EventLink::getByVbId($eventVbId);
        if (!$event) {
            throw new ErrorException('cant_find_event');
        }
        $this->eventId = $event->event_id;
        $this->eventVbId = $eventVbId;
    }

    /**
     * @return int
     */
    public function getEventId():int
    {
        return $this->eventId;
    }

    /**
     * @param string $tid
     * @param array $rounds
     * @return void
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\Api\VirtualBoxing\DuplicateException
     */
    public function setResult(string $tid, array $rounds)
    {
        $sportId = $this->getConfigOption('sport_id');
        if ((new Sport())->checkSportEventExists($sportId, $this->eventId) === false) {
            throw new ErrorException('cant_find_event');
        }
        if (Result::existsById($tid)) {
            throw new DuplicateException(compact($this->eventId, $this->eventVbId, $tid));
        }
        $mapResult = (new ResultMapper($this->config, $this->eventId))->map($rounds);

        \DB::connection('line')->transaction(function () use ($mapResult, $tid) {
            $resultGameData = [];
            foreach ($mapResult['score'] as $resultGame) {
                foreach ($resultGame as $params) {
                    $resultGameData[] = $params;
                }
            }
            if (!$resultGameData || !ResultGame::insert($resultGameData)) {
                throw new ErrorException('cant_insert_result');
            }
            if (!ResultGameTotal::updateResultGameTotal($mapResult['result_game_total'], $mapResult['event_id'])) {
                throw new ErrorException("Can't update result total");
            }
            $resultModel = new Result(['tid' => $tid]);
            if (!$resultModel->save()) {
                throw new ErrorException("Can't insert result");
            }
            ResultGame::updateApprove($this->eventId);
        });
    }
}
