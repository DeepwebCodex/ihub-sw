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

class VfUo extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        'Under' => 11,
        'Over' => 10
    ];

    public function getDParam1() : float
    {
        $required = $this->isParamRequired($this->marketTemplate->market_type_id, $this->marketTemplate->market_type_count, static::D_PARAM_1);

        if($required) {
            return 2.5;
        }

        return 0;
    }

    public function getDParam2() : float
    {
        $required = $this->isParamRequired($this->marketTemplate->market_type_id, $this->marketTemplate->market_type_count, static::D_PARAM_2);

        if($required) {
            return 2.5;
        }

        return 0;
    }
}