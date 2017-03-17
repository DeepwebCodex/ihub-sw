<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Class MapperOW
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
class MapperOW implements IMapper
{
    /**
     * @var
     */
    protected $participantHomeId;
    /**
     * @var
     */
    protected $participantAwayId;

    /**
     * @param mixed $participantHomeId
     */
    public function setParticipantHomeId($participantHomeId)
    {
        $this->participantHomeId = $participantHomeId;
    }

    /**
     * @param mixed $participantAwayId
     */
    public function setParticipantAwayId($participantAwayId)
    {
        $this->participantAwayId = $participantAwayId;
    }

    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function map(int $marketId, array $outcomes, array $selection, IMapResultFormatter $resultFormatter):array
    {
        $outcomeTypeId = null;
        $participantId = null;
        foreach ($outcomes as $outcome) {
            if ($selection['home'] === 'H' && data_get($outcome,'name') === 'Home') {
                $outcomeTypeId = data_get($outcome, 'id');
                $participantId = $this->participantHomeId;
                break;
            }
            if ($selection['home'] === 'A' && data_get($outcome,'name') === 'Away') {
                $outcomeTypeId = data_get($outcome,'id');
                $participantId = $this->participantAwayId;
                break;
            }
            if ($selection['home'] === 'D' && data_get($outcome,'name') === 'Draw') {
                $outcomeTypeId = data_get($outcome,'id');
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 0.00, $participantId);
    }
}
