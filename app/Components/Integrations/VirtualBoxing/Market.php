<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\ConfigTrait;
use App\Exceptions\Api\VirtualBoxing\ErrorException;
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

    /**
     * @var MarketOutcomeMapper
     */
    protected $marketOutcomeMapper;

    /**
     * @var int
     */
    protected $eventId;

    /**
     * Market constructor.
     * @param array $config
     * @param int $participantHomeId
     * @param int $participantAwayId
     */
    public function __construct(array $config, int $participantHomeId, int $participantAwayId)
    {
        $this->config = $config;
        $this->marketOutcomeMapper = new MarketOutcomeMapper($participantHomeId, $participantAwayId);
    }

    /**
     * @param array $markets
     * @param int $eventId
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function setMarkets(array $markets, int $eventId)
    {
        $this->eventId = $eventId;

        foreach ($markets as $market) {
            $marketCode = $market['code'];
            $marketSelections = $market['selection'];
            if (in_array($marketCode, ['CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6'], true)) {
                $this->marketRoundScore($marketSelections, $marketCode);
                continue;
            }
            if (in_array($marketCode, ['R1', 'R2', 'R3', 'R4', 'R5', 'R6'], true)) {
                $this->marketRound($marketSelections, $marketCode);
                continue;
            }
            if (in_array($marketCode, ['CS', 'T65', 'OE', 'KO1', 'KO2', 'KO3', 'KO4'], true)) {
                $this->marketMatchResult($marketSelections, $marketCode);
                continue;
            }
            if ($marketCode === 'OW') {
                $this->marketOutrightWinner($marketSelections, $marketCode);
                continue;
            }
            throw new ErrorException('Unknown market code');
        }
        $this->resumeMarketEvent();
    }

    /**
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function resumeMarketEvent()
    {
        $marketModel = new MarketModel();
        if (!$marketModel->resumeMarketEvent($this->eventId)) {
            throw new ErrorException("Can't update market event");
        }
        $statusType = $this->getConfigOption('status_type');
        $statusDesc = new StatusDesc([
            'status_type' => $statusType,
            'name' => $statusType,
            'event_id' => $this->eventId,
        ]);
        if (!$statusDesc->save()) {
            throw new ErrorException("Can't insert status_desc");
        }
    }

    /**
     * @param array $marketSelections
     * @param string $marketName
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    private function marketOutrightWinner(array $marketSelections, string $marketName)
    {
        $this->create(
            $marketSelections,
            $this->getConfigOption('market.OW_result_type'),
            $this->getConfigOption('market.OW'),
            $marketName
        );
    }

    /**
     * @param array $marketSelections
     * @param string $marketName
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    private function marketRound(array $marketSelections, string $marketName)
    {
        $marketNumber = ltrim($marketName, 'R');
        $this->create(
            $marketSelections,
            $this->getConfigOption('rounds_map')[$marketNumber],
            $this->getConfigOption('market.OW'),
            $marketName
        );
    }

    /**
     * @param array $marketSelections
     * @param string $marketName
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    private function marketRoundScore(array $marketSelections, string $marketName)
    {
        $marketNumber = ltrim($marketName, 'CS');
        $this->create(
            $marketSelections,
            $this->getConfigOption('rounds_map')[$marketNumber],
            $this->getConfigOption('market.CSR'),
            $marketName
        );
    }

    /**
     * @param array $marketSelections
     * @param string $marketName
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    private function marketMatchResult(array $marketSelections, string $marketName)
    {
        $this->create(
            $marketSelections,
            $this->getConfigOption('market.match_result_type_id'),
            $this->getConfigOption('market.' . $marketName),
            $marketName
        );
    }

    /**
     * @param array $marketSelections
     * @param int $resultTypeId
     * @param int $marketTemplateId
     * @param string $marketName
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    private function create(array $marketSelections, int $resultTypeId, int $marketTemplateId, string $marketName)
    {
        $marketModel = MarketModel::create([
            'event_id' => $this->eventId,
            'market_template_id' => $marketTemplateId,
            'result_type_id' => $resultTypeId,
            'max_bet' => $this->getConfigOption('market.max_bet'),
            'max_payout' => $this->getConfigOption('market.max_payout'),
            'stop_loss' => $this->getConfigOption('market.stop_loss'),
            'service_id' => $this->getConfigOption('service_id'),
            'user_id' => $this->getConfigOption('user_id')
        ]);
        $marketId = $marketModel->id;
        if (!$marketId) {
            throw new ErrorException('cant_insert_market');
        }

        $outcomes = (new OutcomeType())->getOutcomeTypeByMarketTemplateId($marketTemplateId);

        foreach ($marketSelections as $selection) {
            if (in_array($marketName, ['OW', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6'], true)) {
                $params = $this->marketOutcomeMapper->mapOWMain($marketId, $outcomes, $selection);
            } elseif (in_array($marketName, ['CS', 'CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6'], true)) {
                $params = $this->marketOutcomeMapper->mapCSMain($marketId, $outcomes, $selection);
            } else {
                $params = $this->marketOutcomeMapper->{'map' . $marketName}($marketId, $outcomes, $selection);
            }
            $outcomeModel = new Outcome($params);
            if (!$outcomeModel->save()) {
                throw new ErrorException('cant_insert_outcome');
            }
        }
    }
}
