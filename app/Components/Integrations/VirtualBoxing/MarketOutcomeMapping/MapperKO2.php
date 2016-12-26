<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Class MapperKO2
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
class MapperKO2 implements IMapper
{
    /**
     * @param int $marketId
     * @param array $outcomes
     * @param array $selection
     * @param IMapResultFormatter $resultFormatter
     * @return array
     */
    public function map(int $marketId, array $outcomes, array $selection, IMapResultFormatter $resultFormatter):array
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
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId);
    }
}
