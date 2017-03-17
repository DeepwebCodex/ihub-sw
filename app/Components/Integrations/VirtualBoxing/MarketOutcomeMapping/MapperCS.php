<?php

namespace App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping;

/**
 * Class MapperCS
 * @package App\Components\Integrations\VirtualBoxing\MarketOutcomeMapping
 */
class MapperCS implements IMapper
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
            $name = str_replace(':', '-', data_get($outcome, 'name'));
            if ($name === (string)data_get($selection, 'name')) {
                $outcomeTypeId = data_get($outcome, 'id');
                break;
            }
        }
        return $resultFormatter->format($marketId, $selection, $outcomeTypeId);
    }
}
