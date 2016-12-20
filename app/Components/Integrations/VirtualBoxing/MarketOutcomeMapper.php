<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping\IMapResultFormatter;
use App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping\MapperCS;
use App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping\MapperOW;

/**
 * Class MarketOutcomeMapper
 * @package App\Components\Integrations\VirtualBoxing
 */
class MarketOutcomeMapper
{
    /**
     * @var int
     */
    protected $participantHomeId;

    /**
     * @var int
     */
    protected $participantAwayId;

    /**
     * @var IMapResultFormatter
     */
    protected $resultFormatter;

    /**
     * MarketOutcomeMapper constructor.
     * @param int $participantHomeId
     * @param int $participantAwayId
     * @param $resultFormatter
     */
    public function __construct(int $participantHomeId, int $participantAwayId, IMapResultFormatter $resultFormatter)
    {
        $this->participantHomeId = $participantHomeId;
        $this->participantAwayId = $participantAwayId;
        $this->resultFormatter = $resultFormatter;
    }

    /**
     * @param string $marketName
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return mixed
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function map(string $marketName, int $marketId, array $outcomes, array $selection)
    {
        if (in_array($marketName, ['OW', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6'], true)) {
            $mapper = new MapperOW();
            $mapper->setParticipantAwayId($this->participantAwayId);
            $mapper->setParticipantHomeId($this->participantHomeId);
            return $mapper->map($marketId, $outcomes, $selection, $this->resultFormatter);
        }
        if (in_array($marketName, ['CS', 'CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6'], true)) {
            return (new MapperCS())->map($marketId, $outcomes, $selection, $this->resultFormatter);
        }
        $mapperClassName = 'App\\Components\\Integrations\\VirtualBoxing\\MarketOutcomeMapping\\Mapper' . $marketName;
        $mapper = new $mapperClassName;

        if ($marketName === 'KO4') {
            $mapper->setParticipantAwayId($this->participantAwayId);
            $mapper->setParticipantHomeId($this->participantHomeId);
        }

        return $mapper->map($marketId, $outcomes, $selection, $this->resultFormatter);
    }
}
