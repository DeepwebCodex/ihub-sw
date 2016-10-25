<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\ConfigTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Line\Market as MarketModel;
use App\Models\Line\Outcome;
use App\Models\Line\OutcomeType;
use App\Models\Line\StatusDesc;

/**
 * Class Market
 * @package App\Components\Integrations\VirtualSports
 */
class Market
{
    use ConfigTrait;

    protected $error;

    /**
     * Market constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $markets
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @return void
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function setMarkets(array $markets, $eventId, $participant1, $participant2):void
    {
        foreach ($markets as $market) {
            $marketCode = $market['code'];
            if (in_array($marketCode, ['CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6'], true)) {
                $this->market_CS_main($market, $eventId, $participant1, $participant2, $marketCode);
                return;
            } elseif (in_array($marketCode, ['R1', 'R2', 'R3', 'R4', 'R5', 'R6'], true)) {
                $this->market_R($market, $eventId, $participant1, $participant2, $marketCode);
                return;
            }
            $functionName = 'market_' . $marketCode;
            if (is_callable([$this, $functionName])) {
                $this->{$functionName}($market, $eventId, $participant1, $participant2, $marketCode);
            }
        }
    }

    /**
     * @param int $eventId
     * @return void
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function suspendMarkets(int $eventId):void
    {
        $marketModel = new MarketModel();
        if (!$marketModel->updateMarketEvent($eventId)) {
            throw new ApiHttpException(400, "Can't update market event");
        }
        $statusType = $this->getConfigOption('status_type');
        $statusDesc = new StatusDesc([
            'status_type' => $statusType,
            'name' => $statusType,
            'event_id' => $eventId,
        ]);
        if (!$statusDesc->save()) {
            throw new ApiHttpException(400, "Can't insert status_desc");
        }
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $resultTypeId
     * @param $marketTemplateId
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function create(
        $market,
        $eventId,
        $participant1,
        $participant2,
        $resultTypeId,
        $marketTemplateId,
        $marketName
    ) {
        $marketModel = MarketModel::create([
            'event_id' => $eventId,
            'market_template_id' => $marketTemplateId,
            'result_type_id' => $resultTypeId,
            'max_bet' => $this->getConfigOption('market_max_bet'),
            'max_payout' => $this->getConfigOption('market_max_payout'),
            'stop_loss' => $this->getConfigOption('market_stop_loss'),
            'service_id' => $this->getConfigOption('service_id'),
        ]);
        $marketId = $marketModel->id;
        if (!$marketId) {
            throw new ApiHttpException(400, 'cant_insert_market');
        }

        $outcomes = (new OutcomeType())->getOutcomeType($marketTemplateId);

        foreach ($market->selection as $selection) {
            if (in_array($marketName, ['ow', 'r1', 'r2', 'r3', 'r4', 'r5', 'r6'], true)) {
                $params = $this->map_ow_main($marketId, $outcomes, $selection, $participant1, $participant2);
            } elseif (in_array($marketName, ['cs', 'cs1', 'cs2', 'cs3', 'cs4', 'cs5', 'cs6'], true)) {
                $params = $this->map_cs_main($marketId, $outcomes, $selection, $participant1, $participant2);
            } else {
                $params = $this->{'map_' . $marketName}($marketId, $outcomes, $selection, $participant1, $participant2);
            }
            if ($params === false) {
                throw new ApiHttpException(400, $this->error);
            }
            $outcomeModel = new Outcome($params);
            if (!$outcomeModel->save()) {
                throw new ApiHttpException(400, 'cant_insert_outcome');
            }
        }
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_OW($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.OW_result_type'),
            $this->getConfigOption('market.OW'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function map_ow_main($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ($selection['home'] === 'H' && $value['name'] === 'Home') {
                $outcomeTypeId = $value['id'];
                $participant = $participant1;
                break;
            } else {
                if ($selection['home'] === 'A' && $value['name'] === 'Away') {
                    $outcomeTypeId = $value['id'];
                    $participant = $participant2;
                    break;
                } else {
                    if ($selection['home'] === 'D' && $value['name'] === 'Draw') {
                        $outcomeTypeId = $value['id'];
                        $participant = null;
                        break;
                    }
                }
            }
        }
        if (!$outcomeTypeId) {
            throw new ApiHttpException(400, 'cant_find_outcome');
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => $participant,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 0.00
        ];
        return $res;
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_R($market, $eventId, $participant1, $participant2, $marketName)
    {
        $marketNumber = ltrim($marketName, 'R');
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('rounds_map')[$marketNumber],
            $this->getConfigOption('market.OW'),
            $marketName
        );
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_CS($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market_match_rt_id'),
            $this->getConfigOption('market_CS'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_cs_main($marketId, $outcomes, $selection, $participant1, $participant2)
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
        if (!$outcomeTypeId) {
            $this->error = 'cant_find_outcome';
            return false;
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => null,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 0.00
        ];
        return $res;
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_CS_main($market, $eventId, $participant1, $participant2, $marketName)
    {
        $marketNumber = ltrim($marketName, 'CS');
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('rounds_map')[$marketNumber],
            $this->getConfigOption('market.CSR'),
            $marketName
        );
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_T65($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.match_rt_id'),
            $this->getConfigOption('market.T65'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_t65($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === 'Under 6.5' && $value['name'] === 'Under') {
                $outcomeTypeId = $value['id'];
                break;
            } else {
                if ((string)$selection['name'] === 'Over 6.5' && $value['name'] === 'Over') {
                    $outcomeTypeId = $value['id'];
                    break;
                }
            }
        }
        if (!$outcomeTypeId) {
            $this->error = 'cant_find_outcome';
            return false;
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => null,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 6.5
        ];
        return $res;
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_OE($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.match_rt_id'),
            $this->getConfigOption('market.OE'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_oe($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === 'Even' && $value['name'] === 'Even') {
                $outcomeTypeId = $value['id'];
                break;
            } else {
                if ((string)$selection['name'] === 'Odd' && $value['name'] === 'Odd') {
                    $outcomeTypeId = $value['id'];
                    break;
                }
            }
        }
        if (!$outcomeTypeId) {
            $this->error = 'cant_find_outcome';
            return false;
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => null,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 6.5
        ];
        return $res;
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_KO1($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.match_rt_id'),
            $this->getConfigOption('market.KO1'),
            $marketName
        );
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_KO2($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.match_rt_id'),
            $this->getConfigOption('market.KO2'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_ko2($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === 'Y' && $value['name'] === 'Yes') {
                $outcomeTypeId = $value['id'];
                break;
            } else {
                if ((string)$selection['name'] === 'N' && $value['name'] === 'No') {
                    $outcomeTypeId = $value['id'];
                    break;
                }
            }
        }
        if (!$outcomeTypeId) {
            $this->error = 'cant_find_outcome';
            return false;
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => null,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 0.00
        ];
        return $res;
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_KO3($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.match_rt_id'),
            $this->getConfigOption('market.KO3'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_ko3($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        return $this->map_ko1($marketId, $outcomes, $selection, $participant1, $participant2);
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_ko1($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ((string)$selection['name'] === $value['name']) {
                $outcomeTypeId = $value['id'];
                break;
            }
        }
        if (!$outcomeTypeId) {
            $this->error = 'cant_find_outcome';
            return false;
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => null,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 0.00
        ];
        return $res;
    }

    /**
     * @param $market
     * @param $eventId
     * @param $participant1
     * @param $participant2
     * @param $marketName
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    private function market_KO4($market, $eventId, $participant1, $participant2, $marketName)
    {
        $this->create(
            $market,
            $eventId,
            $participant1,
            $participant2,
            $this->getConfigOption('market.OW_result_type'),
            $this->getConfigOption('market.KO4'),
            $marketName
        );
    }

    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @param $participant1
     * @param $participant2
     * @return array|bool
     */
    private function map_ko4($marketId, $outcomes, $selection, $participant1, $participant2)
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $value) {
            if ($selection['home'] === 'H' && $value['name'] === 'Home') {
                $outcomeTypeId = $value['id'];
                $participant = $participant1;
                break;
            } else {
                if ($selection['home'] === 'A' && $value['name'] === 'Away') {
                    $outcomeTypeId = $value['id'];
                    $participant = $participant2;
                    break;
                } else {
                    if ($selection['home'] === 'D' && $value['name'] === 'no knockout') {
                        $outcomeTypeId = $value['id'];
                        $participant = null;
                        break;
                    }
                }
            }
        }
        if (!$outcomeTypeId) {
            $this->error = 'cant_find_outcome';
            return false;
        }

        $res = [
            'event_market_id' => $marketId,
            'event_participant_id' => $participant,
            'outcome_type_id' => $outcomeTypeId,
            'coef' => (string)$selection->price['dec'],
            'dparam1' => 0.00
        ];
        return $res;
    }
}
