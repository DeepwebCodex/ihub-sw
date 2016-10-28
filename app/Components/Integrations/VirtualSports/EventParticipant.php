<?php

namespace App\Components\Integrations\VirtualSports;

use App\Exceptions\Api\ApiHttpException;

/**
 * Class EventParticipant
 * @package App\Components\Integrations\VirtualSports
 */
class EventParticipant
{
    /**
     * @var EventParticipant
     */
    protected $eventParticipantModel;

    /**
     * Participant constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param int $number
     * @param int $eventId
     * @param string $participantName
     * @return void
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function create(int $number, int $eventId, string $participantName):void
    {
        $participant = new Participant($this->config);
        $participant->create($participantName);
        $participantId = $participant->getParticipantId();

        if ($participantId) {
            $eventParticipant = new EventParticipant([
                'number' => $number,
                'participant_id' => $participantId,
                'event_id' => $eventId
            ]);
            if (!$eventParticipant->save()) {
                throw new ApiHttpException(400, 'error_create_participant2');
            }
            $this->eventParticipantModel = $eventParticipant;
        }
        throw new ApiHttpException(400, 'error_create_participant');
    }

    /**
     * @return int
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function getEventParticipantId():int
    {
        if (!$this->eventParticipantModel) {
            throw new ApiHttpException(400, 'error_create_participant');
        }
        return (int)$this->eventParticipantModel->id;
    }
}
