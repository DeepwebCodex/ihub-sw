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
            if ((string)data_get($selection, 'name') === 'Even' && data_get($outcome, 'name') === 'Even') {
                $outcomeTypeId = data_get($outcome, 'id');
                break;
            }
            if ((string)data_get($selection, 'name') === 'Odd' && data_get($outcome, 'name') === 'Odd') {
                $outcomeTypeId = data_get($outcome, 'id');
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId, 6.5);
    }
}
