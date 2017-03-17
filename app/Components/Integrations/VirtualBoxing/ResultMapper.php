<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Traits\ConfigTrait;
use App\Models\Line\Event;

/**
 * Class ResultMapper
 * @package App\Components\Integrations\VirtualBoxing
 */
class ResultMapper
{
    use ConfigTrait;

    protected $eventId;

    protected $userId;

    /**
     * @var array
     */
    protected $participants;

    /**
     * ResultService constructor.
     * @param array $config
     * @param $eventId
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     */
    public function __construct(array $config, $eventId)
    {
        $this->config = $config;
        $this->eventId = $eventId;
        $this->userId = $this->getConfigOption('user_id');
    }

    /**
     * @param array $rounds
     * @return array
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function map(array $rounds): array
    {
        $answer = [];
        $eventParticipants = (new Event())->preGetParticipant($this->eventId);
        $this->participants = [
            '1' => data_get($eventParticipants, '0.event_participant_id'),
            '2' => data_get($eventParticipants, '1.event_participant_id'),
        ];
        $totalStr = 't1:t2 (rp11:rp12)(rp21:rp22)(rp31:rp32)(rp41:rp42)(rp51:rp52)(rp61:rp62)';
        $total = $roundPoint = [1 => 0, 2 => 0];

        foreach ($rounds as $round) {
            $resultType = $this->mapResultType(data_get($round,'round'));
            $answer['score'][$resultType] = $this->prepareInsertEmpty($resultType);
            foreach (data_get($round,'participant') as $participant) {
                $participantId = data_get($participant,'id');
                $this->checkParticipantPoint($participant, $resultType, $participantId, $answer, $total, $roundPoint);
                $this->checkParticipantKnockdown($participant, $resultType, $participantId, $answer);
                $totalStr = str_replace('rp' . data_get($round,'round') . $participantId, $roundPoint[$participantId], $totalStr);
            }
            $roundPoint = [1 => 0, 2 => 0];

            $this->checkParticipantWinner($round, $resultType, $answer);
        }
        foreach ($total as $participantId => $item) {
            $totalStr = str_replace('t' . $participantId, $item, $totalStr);
        }
        $answer['result_game_total'] = [
            'event_id' => $this->eventId,
            'result_total' => $totalStr,
            'result_type_id' => $this->mapResultType(6),
        ];
        $answer['event_id'] = $this->eventId;

        return $answer;
    }

    /**
     * @param int $round
     * @return int
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function mapResultType(int $round): int
    {
        return $this->getConfigOption('rounds_map')[$round];
    }

    /**
     * @param int $resultType
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     */
    protected function prepareInsertEmpty(int $resultType): array
    {
        $scopeType = $this->getConfigOption('scope_type');
        $res = [];
        foreach ($this->participants as $participant) {
            foreach (array_keys($scopeType) as $scope) {
                $res[] = [
                    'event_id' => $this->eventId,
                    'scope_data_id' => $this->mapScope($scope),
                    'result_type_id' => $resultType,
                    'event_particpant_id' => $participant,
                    'amount' => 0,
                    'staff_id' => $this->userId,
                    'result_time' => 0
                ];
            }
        }
        return $res;
    }

    /**
     * @param string $scope
     * @return int
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function mapScope(string $scope): int
    {
        return $this->getConfigOption('scope_type')[$scope];
    }

    /**
     * @param $participant
     * @param $resultType
     * @param $participantId
     * @param $answer
     * @param $total
     * @param $roundPoint
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     */
    protected function checkParticipantPoint($participant, $resultType, $participantId, &$answer, &$total, &$roundPoint)
    {
        if ((int)$participant['point'] === 1) {
            $answer['score'][$resultType][] = [
                'event_id' => $this->eventId,
                'scope_data_id' => $this->mapScope('point'),
                'result_type_id' => $resultType,
                'event_particpant_id' => $this->participants[$participantId],
                'amount' => 1,
                'staff_id' => $this->userId,
                'result_time' => 0
            ];
            ++$total[$participantId];
            ++$roundPoint[$participantId];
        }
    }

    /**
     * @param $participant
     * @param $resultType
     * @param $participantId
     * @param $answer
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     */
    protected function checkParticipantKnockdown($participant, $resultType, $participantId, &$answer)
    {
        if ((int)$participant['knockdown'] === 1) {
            $answer['score'][$resultType][] = [
                'event_id' => $this->eventId,
                'scope_data_id' => $this->mapScope('knockdown'),
                'result_type_id' => $resultType,
                'event_particpant_id' => $this->participants[$participantId],
                'amount' => 1,
                'staff_id' => $this->userId,
                'result_time' => 0
            ];
        }
    }

    /**
     * @param $round
     * @param $resultType
     * @param $answer
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     */
    protected function checkParticipantWinner($round, $resultType, &$answer)
    {
        if (data_get($round,'status') !== 'Draw') {
            $answer['score'][$resultType][] = [
                'event_id' => $this->eventId,
                'scope_data_id' => $this->mapScope('winner'),
                'result_type_id' => $resultType,
                'event_particpant_id' => $this->participants[(string)data_get($round,'status')],
                'amount' => 1,
                'staff_id' => $this->userId,
                'result_time' => 0
            ];
        }
    }
}
