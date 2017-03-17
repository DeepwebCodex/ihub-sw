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
            if (data_get($selection, 'home') === 'H' && data_get($outcome,'name') === 'Home') {
                $outcomeTypeId = data_get($outcome, 'id');
                $participantId = $this->participantHomeId;
                break;
            }
            if (data_get($selection, 'home') === 'A' && data_get($outcome,'name') === 'Away') {
                $outcomeTypeId = data_get($outcome, 'id');
                $participantId = $this->participantAwayId;
                break;
            }
            if (data_get($selection, 'home') === 'D' && data_get($outcome,'name') === 'no knockout') {
                $outcomeTypeId = data_get($outcome, 'id');
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 0.0, $participantId);
    }
}
