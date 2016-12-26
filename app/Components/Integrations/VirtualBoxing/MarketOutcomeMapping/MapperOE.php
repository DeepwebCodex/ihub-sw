<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Class MapperOE
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
class MapperOE implements IMapper
{
    /**
     * @param $marketId
     * @param $outcomes
     * @param $selection
     * @return array
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function map(int $marketId, array $outcomes, array $selection, IMapResultFormatter $resultFormatter):array
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
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 6.5);
    }
}
