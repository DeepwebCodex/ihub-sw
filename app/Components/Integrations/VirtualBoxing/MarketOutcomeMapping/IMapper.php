<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Interface IMapper
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
interface IMapper
{
    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @param IMapResultFormatter $resultFormatter
     * @return array
     */
    public function map(int $marketId, array $outcomes, array $selection, IMapResultFormatter $resultFormatter): array;
}
