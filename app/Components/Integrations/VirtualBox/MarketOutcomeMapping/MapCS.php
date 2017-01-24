<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\VirtualBox\MarketOutcomeMapping;

use App\Components\Integrations\VirtualSports\BaseMarketOutcomeMapper;
use App\Components\Integrations\VirtualSports\Interfaces\MarketOutcomeMapInterface;
use App\Models\Line\OutcomeType;

class MapCS extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected function getOutcomeType() : OutcomeType
    {
        $outcomeName = array_get($this->outcome, array_get($this->outcomeConfig, 'outcomeFiled'));

        $name = str_replace("-", ":", $outcomeName);

        $outcomeType = $this->outcomeTypes->where('name', $name)->first();

        if($outcomeType === null) {
            $this->failedToGetOutcomeType();
        }

        return $outcomeType;
    }
}