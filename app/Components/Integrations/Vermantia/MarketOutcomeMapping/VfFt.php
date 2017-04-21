<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\Vermantia\MarketOutcomeMapping;

use App\Components\Integrations\VirtualSports\BaseMarketOutcomeMapper;
use App\Components\Integrations\VirtualSports\Interfaces\MarketOutcomeMapInterface;

class VfFt extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        'HOME' => 1,
        'AWAY' => 3,
        'No Goal' => 250
    ];

    public function getIParam1() : int
    {
        $required = $this->isParamRequired($this->marketTemplate->market_type_id, $this->marketTemplate->market_type_count, static::I_PARAM_1);

        if($required) {
            return 1;
        }

        return 0;
    }

    public function getIParam2() : int
    {
        $required = $this->isParamRequired($this->marketTemplate->market_type_id, $this->marketTemplate->market_type_count, static::I_PARAM_2);

        if($required) {
            return 1;
        }

        return 0;
    }
}