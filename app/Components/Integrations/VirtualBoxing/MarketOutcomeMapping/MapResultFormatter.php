<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

use App\Exceptions\Api\VirtualBoxing\ErrorException;

class MapResultFormatter implements IMapResultFormatter
{
    public function format(
        int $marketId,
        array $selection,
        int $outcomeTypeId,
        float $dParam = 0.0,
        $participantId = null
    ): array {
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
