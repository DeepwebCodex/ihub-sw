<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\ConfigTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Line\Event;
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
     * ResultService constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param int $eventVbId
     * @param string $tid
     * @param array $rounds
     * @throws \Exception
     */
    public function setResult(int $eventVbId, string $tid, array $rounds):void
    {
        $event = EventLink::getByVbId($eventVbId);
        if (!$event) {
            throw new ApiHttpException(400, 'cant_find_event');
        }
        $eventId = $event->event_id;
        $this->eventId = $eventId;
        $sportId = $this->getConfigOption('sport_id');
        if ((new Sport())->checkSportEventExists($sportId, $eventId) === false) {
            throw new ApiHttpException(400, 'cant_find_event');
        }

        if (Result::existsById($tid)) {
            throw new ApiHttpException(200, 'done_duplicate', compact($eventId, $eventVbId, $tid));
        }
        $mapResult = $this->mapResult($eventId, $rounds);

        \DB::connection('line')->transaction(function () use ($mapResult, $tid, $eventId) {
            foreach ($mapResult['score'] as $rt) {
                foreach ($rt as $params) {
                    $resultGameModel = new ResultGame($params);
                    if (!$resultGameModel->save()) {
                        throw new ApiHttpException(400, 'cant_insert_result');
                    }
                }
            }
            if (!ResultGameTotal::updateResultGameTotal($mapResult['result_game_total'], $mapResult['event_id'])) {
                throw new ApiHttpException(400, "Can't update result total");
            }

            $resultModel = new Result(['tid' => $tid]);
            if (!$resultModel->save()) {
                throw new ApiHttpException(400, "Can't insert result");
            }

            ResultGame::updateApprove($eventId);
        });
    }

    /**
     * @param int $eventId
     * @param array $rounds
     * @return array
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function mapResult(int $eventId, array $rounds):array
    {
        $answer = [];
        $eventParticipants = (new Event())->preGetParticipant($eventId);
        $participants = [
            '1' => $eventParticipants[0]['event_participant_id'],
            '2' => $eventParticipants[1]['event_participant_id'],
        ];
        $totalStr = 't1:t2 (rp11:rp12)(rp21:rp22)(rp31:rp32)(rp41:rp42)(rp51:rp52)(rp61:rp62)';
        $total = $roundPoint = [1 => 0, 2 => 0];

        $userId = $this->getConfigOption('user_id');

        //Start mapping round
        foreach ($rounds as $round) {
            $resultType = $this->mapResultType($round['round']);
            //must insert init
            $answer['score'][$resultType] = $this->prepareInsertEmpty(
                $eventId,
                $participants,
                $resultType
            );
            foreach ($round['participant'] as $participant) {
                $participantId = $participant['id'];

                //point
                if ((int)$participant['point'] === 1) {
                    $answer['score'][$resultType][] = [
                        'event_id' => $eventId,
                        'scope_data_id' => $this->mapScope('point'),
                        'result_type_id' => $resultType,
                        'event_particpant_id' => $participants[$participantId],
                        'amount' => 1,
                        'staff_id' => $userId,
                        'result_time' => 0
                    ];
                    ++$total[$participantId];
                    ++$roundPoint[$participantId];
                }

                //knockdown
                if ((int)$participant['knockdown'] === 1) {
                    $answer['score'][$resultType][] = [
                        'event_id' => $eventId,
                        'scope_data_id' => $this->mapScope('knockdown'),
                        'result_type_id' => $resultType,
                        'event_particpant_id' => $participants[$participantId],
                        'amount' => 1,
                        'staff_id' => $userId,
                        'result_time' => 0
                    ];
                }
                $totalStr = str_replace(
                    'rp' . $round['round'] . $participantId,
                    $roundPoint[$participantId],
                    $totalStr
                );
            }

            //reset point
            $roundPoint = [1 => 0, 2 => 0];

            //winner
            if ($round['status'] !== 'Draw') {
                $answer['score'][$resultType][] = [
                    'event_id' => $eventId,
                    'scope_data_id' => $this->mapScope('winner'),
                    'result_type_id' => $resultType,
                    'event_particpant_id' => $participants[(string)$round['status']],
                    'amount' => 1,
                    'staff_id' => $userId,
                    'result_time' => 0
                ];
            }
        }
        foreach (array_keys($total) as $participantId) {
            $totalStr = str_replace('t' . $participantId, $total[$participantId], $totalStr);
        }
        //result_game_total
        $answer['result_game_total'] = [
            'event_id' => $eventId,
            'result_total' => $totalStr,
            'result_type_id' => $this->mapResultType(6),
        ];
        $answer['event_id'] = $eventId;

        return $answer;
    }

    /**
     * @param $round
     * @return int
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function mapResultType(int $round):int
    {
        return $this->getConfigOption('rounds_map')[$round];
    }

    /**
     * @param int $eventId
     * @param array $participants
     * @param int $resultType
     * @return array
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function prepareInsertEmpty(int $eventId, array $participants, int $resultType):array
    {
        $scopeType = $this->getConfigOption('scope_type');
        $res = [];
        foreach ($participants as $participant) {
            foreach (array_keys($scopeType) as $scope) {
                $res[] = [
                    'event_id' => $eventId,
                    'scope_data_id' => $this->mapScope($scope),
                    'result_type_id' => $resultType,
                    'event_particpant_id' => $participant,
                    'amount' => 0,
                    'result_time' => 0
                ];
            }
        }
        return $res;
    }

    /**
     * @param string $scope
     * @return int
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function mapScope(string $scope):int
    {
        return $this->getConfigOption('scope_type')[$scope];
    }

    /**
     * @param $eventId
     * @return void
     * @throws \Exception
     */
    public function initResultEvent(int $eventId):void
    {
        $eventType = $this->getConfigOption('event_type');

        $event = new Event();

        $scopeData = $event->preGetScope($eventId, $eventType);
        if (!$scopeData) {
            throw new ApiHttpException(400, 'init_result');
        }

        $participant = $event->preGetParticipant($eventId);
        if (!$participant) {
            throw new ApiHttpException(400, 'init_result');
        }

        $resultType = $event->preGetPeriodStart($eventId, $eventType);
        if (!$resultType) {
            throw new ApiHttpException(400, 'init_result');
        }

        (new ResultGame)->checkResultTable($eventId, $resultType, $participant, $scopeData);

        ResultGameTotal::insertResultGameTotal($eventId, [
            'result_total' => '',
            'result_total_json' => '',
            'result_type_id' => $resultType[0]['id']
        ]);
    }
}
