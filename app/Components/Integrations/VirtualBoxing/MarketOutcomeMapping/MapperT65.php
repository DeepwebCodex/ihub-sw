<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Class MapperT65
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
class MapperT65 implements IMapper
{
    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function map(int $marketId, array $outcomes, array $selection, IMapResultFormatter $resultFormatter):array
    {
        $outcomeTypeId = null;
        foreach ($outcomes as $outcome) {
            if ((string)data_get($selection, 'name') === 'Under 6.5' && data_get($outcome, 'name') === 'Under') {
                $outcomeTypeId = data_get($outcome, 'id');
                break;
            }
            if ((string)data_get($selection, 'name') === 'Over 6.5' && data_get($outcome, 'name') === 'Over') {
                $outcomeTypeId = data_get($outcome, 'id');
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 6.5);
    }
}
