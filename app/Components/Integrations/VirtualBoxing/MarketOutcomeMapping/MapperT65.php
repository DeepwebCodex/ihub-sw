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
            if ((string)$selection['name'] === 'Under 6.5' && $outcome['name'] === 'Under') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
            if ((string)$selection['name'] === 'Over 6.5' && $outcome['name'] === 'Over') {
                $outcomeTypeId = $outcome['id'];
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 6.5);
    }
}
