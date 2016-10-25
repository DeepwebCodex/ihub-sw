<?php

namespace App\Components\Integrations\VirtualSports;

use App\Exceptions\Api\ApiHttpException;
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function create(string $participantName)
    {
        Translate::add($participantName);

        $participant = new ParticipantModel([
            'name' => $participantName,
            'type' => $this->getConfigOption('type_participant'),
            'country_id' => $this->getConfigOption('country_id'),
            'sport_id' => $this->getConfigOption('sport_id')
        ]);
        if (!$participant->save()) {
            throw new ApiHttpException(400, 'error_create_participant');
        }
        $this->participantModel = $participant;
    }

    /**
     * @return mixed
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function getParticipantId()
    {
        if (!$this->participantModel) {
            throw new ApiHttpException(400, 'error_create_participant');
        }
        return $this->participantModel->id;
    }
}
