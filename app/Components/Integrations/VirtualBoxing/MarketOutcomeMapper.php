<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Exceptions\Api\VirtualBoxing\ErrorException;

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
     * MarketOutcomeMapper constructor.
     * @param int $participantHomeId
     * @param int $participantAwayId
     */
    public function __construct(int $participantHomeId, int $participantAwayId)
    {
        $this->participantHomeId = $participantHomeId;
        $this->participantAwayId = $participantAwayId;
    }

    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapOWMain(int $marketId, array $outcomes, array $selection):array
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
            if ($selection['home'] === 'D' && $outcome['name'] === 'Draw') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId, 0.00, $participantId);
    }

    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapCSMain(int $marketId, array $outcomes, array $selection):array
    {
        //0-5 4-5  2-5 1-5 6:6 6-5 5-5 5-4 5-6 5-1 5-0 5-3 5-2  3-5
        $outcomeTypeId = null;
        foreach ($outcomes as $outcome) {
            $name = str_replace(':', '-', $outcome['name']);
            if ($name === (string)$selection['name']) {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId);
    }

    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapT65(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $outcome) {
            if ((string)$selection['name'] === 'Under 6.5' && $outcome['name'] === 'Under') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
            if ((string)$selection['name'] === 'Over 6.5' && $outcome['name'] === 'Over') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId, 6.5);
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapOE(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $outcome) {
            if ((string)$selection['name'] === 'Even' && $outcome['name'] === 'Even') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
            if ((string)$selection['name'] === 'Odd' && $outcome['name'] === 'Odd') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId, 6.5);
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapKO1(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $outcome) {
            if ((string)$selection['name'] === $outcome['name']) {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId);
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapKO2(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $outcome) {
            if ((string)$selection['name'] === 'Y' && $outcome['name'] === 'Yes') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
            if ((string)$selection['name'] === 'N' && $outcome['name'] === 'No') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId);
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapKO3(int $marketId, array $outcomes, array $selection):array
    {
        return $this->mapKO1($marketId, $outcomes, $selection);
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function mapKO4(int $marketId, array $outcomes, array $selection):array
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
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId, 0.0, $participantId);
    }

    /**
     * @param int $marketId
     * @param array $selection
     * @param int $outcomeTypeId
     * @param float $dParam
     * @param $participantId
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function formatMapResult(
        int $marketId,
        array $selection,
        int $outcomeTypeId,
        float $dParam = 0.0,
        $participantId = null
    ):array
    {
        if (!$outcomeTypeId) {
            throw new ErrorException('cant_find_outcome');
        }
        return [
            'event_market_id' => $marketId,
            'event_participant_id' => $participantId,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection['price']['dec'],
            'dparam1' => $dParam
        ];
    }
}
