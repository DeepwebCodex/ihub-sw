<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Interface IMapResultFormatter
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
interface IMapResultFormatter
{
    public function format(
        int $marketId,
        array $selection,
        int $outcomeTypeId,
        float $dParam = 0.0,
        $participantId = null
    ): array;
}
