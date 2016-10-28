<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Exceptions\Api\ApiHttpException;

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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapOWMain(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ($selection['home'] === 'H' && $value['name'] === 'Home') {
                $outcomeTypeId = $value['id'];
                $participant = $this->participantHomeId;
                break;
            }
            if ($selection['home'] === 'A' && $value['name'] === 'Away') {
                $outcomeTypeId = $value['id'];
                $participant = $this->participantAwayId;
                break;
            }
            if ($selection['home'] === 'D' && $value['name'] === 'Draw') {
                $outcomeTypeId = $value['id'];
                $participant = null;
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId, 0.00, $participant);
    }

    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return array
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapCSMain(int $marketId, array $outcomes, array $selection):array
    {
        //0-5 4-5  2-5 1-5 6:6 6-5 5-5 5-4 5-6 5-1 5-0 5-3 5-2  3-5
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            $name = str_replace(':', '-', $value['name']);
            if ($name === (string)$selection['name']) {
                $outcomeTypeId = $value['id'];
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapT65(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === 'Under 6.5' && $value['name'] === 'Under') {
                $outcomeTypeId = $value['id'];
                break;
            }
            if ((string)$selection['name'] === 'Over 6.5' && $value['name'] === 'Over') {
                $outcomeTypeId = $value['id'];
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapOE(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === 'Even' && $value['name'] === 'Even') {
                $outcomeTypeId = $value['id'];
                break;
            }
            if ((string)$selection['name'] === 'Odd' && $value['name'] === 'Odd') {
                $outcomeTypeId = $value['id'];
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapKO1(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === $value['name']) {
                $outcomeTypeId = $value['id'];
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapKO2(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === 'Y' && $value['name'] === 'Yes') {
                $outcomeTypeId = $value['id'];
                break;
            }
            if ((string)$selection['name'] === 'N' && $value['name'] === 'No') {
                $outcomeTypeId = $value['id'];
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
     * @throws \App\Exceptions\Api\ApiHttpException
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
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function mapKO4(int $marketId, array $outcomes, array $selection):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ($selection['home'] === 'H' && $value['name'] === 'Home') {
                $outcomeTypeId = $value['id'];
                $participant = $this->participantHomeId;
                break;
            }
            if ($selection['home'] === 'A' && $value['name'] === 'Away') {
                $outcomeTypeId = $value['id'];
                $participant = $this->participantAwayId;
                break;
            }
            if ($selection['home'] === 'D' && $value['name'] === 'no knockout') {
                $outcomeTypeId = $value['id'];
                $participant = null;
                break;
            }
        }
        return $this->formatMapResult($marketId, $selection, $outcomeTypeId, 0.0, $participant);
    }

    /**
     * @param $marketId
     * @param $selection
     * @param $outcomeTypeId
     * @param float $dParam
     * @param $participant
     * @return array
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function formatMapResult(
        int $marketId,
        array $selection,
        $outcomeTypeId,
        float $dParam = 0.0,
        $participant = null
    ):array
    {
        if (!$outcomeTypeId) {
            throw new ApiHttpException(400, 'cant_find_outcome');
        }
        return [
            'event_market_id' => $marketId,
            'event_participant_id' => $participant,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection['price']['dec'],
            'dparam1' => $dParam
        ];
    }
}
