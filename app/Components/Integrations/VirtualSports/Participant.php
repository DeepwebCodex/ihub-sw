<?php

namespace App\Components\Integrations\VirtualSports;

use App\Components\Traits\ConfigTrait;
use App\Models\Line\Participant as ParticipantModel;

/**
 * Class Participant
 * @package App\Components\Integrations\VirtualSports
 */
class Participant
{
    use ConfigTrait;

    /**
     * @var ParticipantModel
     */
    protected $participantModel;

    /**
     * Participant constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $participantName
     * @return bool
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function create(string $participantName):bool
    {
        Translate::add($participantName);

        $participant = new ParticipantModel([
            'name' => $participantName,
            'type' => $this->getConfigOption('type_participant'),
            'country_id' => $this->getConfigOption('country_id'),
            'sport_id' => $this->getConfigOption('sport_id')
        ]);
        if (!$participant->save()) {
            return false;
        }
        $this->participantModel = $participant;
        return true;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getParticipantId():int
    {
        if (!$this->participantModel) {
            throw new \RuntimeException('Participant not defined');
        }
        return $this->participantModel->id;
    }
}
