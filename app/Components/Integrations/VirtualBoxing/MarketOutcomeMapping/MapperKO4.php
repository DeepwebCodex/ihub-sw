<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Class MapperKO4
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
class MapperKO4 implements IMapper
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
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function map(int $marketId, array $outcomes, array $selection, IMapResultFormatter $resultFormatter):array
    {
        $outcomeTypeId = null;
        $participantId = null;
        foreach ($outcomes as $outcome) {
            if ($selection['home'] === 'H' && $outcome['name'] === 'Home') {
                $outcomeTypeId = $outcome['id'];
                $participantId = $this->participantHomeId;
                break;
            }
            if ($selection['home'] === 'A' && $outcome['name'] === 'Away') {
                $outcomeTypeId = $outcome['id'];
                $participantId = $this->participantAwayId;
                break;
            }
            if ($selection['home'] === 'D' && $outcome['name'] === 'no knockout') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 0.0, $participantId);
    }
}
