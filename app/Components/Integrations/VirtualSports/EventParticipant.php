<?php

namespace App\Components\Integrations\VirtualSports;

use App\Models\Line\EventParticipant as EventParticipantModel;

/**
 * Class EventParticipant
 * @package App\Components\Integrations\VirtualSports
 */
class EventParticipant
{
    /**
     * @var EventParticipantModel
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
     * @return bool
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function create(int $number, int $eventId, string $participantName):bool
    {
        $participant = new Participant($this->config);
        if (!$participant->create($participantName)) {
            return false;
        }
        $participantId = $participant->getParticipantId();

        if ($participantId) {
            $eventParticipant = new EventParticipantModel([
                'number' => $number,
                'participant_id' => $participantId,
                'event_id' => $eventId
            ]);
            if ($eventParticipant->save()) {
                $this->eventParticipantModel = $eventParticipant;
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getEventParticipantId():int
    {
        if (!$this->eventParticipantModel) {
            throw new \RuntimeException('Event participant not defined');
        }
        return (int)$this->eventParticipantModel->id;
    }
}
