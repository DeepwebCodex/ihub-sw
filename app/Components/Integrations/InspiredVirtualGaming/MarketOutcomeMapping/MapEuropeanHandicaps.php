<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping;

use App\Components\Integrations\VirtualSports\BaseMarketOutcomeMapper;
use App\Components\Integrations\VirtualSports\Interfaces\MarketOutcomeMapInterface;

class MapEuropeanHandicaps extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        'win'   => 1,
        'draw'  => 2,
        'lose'  => 3,
        'WIN'   => 1,
        'DRAW'  => 2,
        'LOSE'  => 3
    ];

    public function getIParam1(): int
    {
        $required = $this->isParamRequired($this->marketTemplate->market_type_id, $this->marketTemplate->market_type_count, static::I_PARAM_1);

        if($required) {
            if(isset($this->mappedMarketsWithOutcomes['wdls'])) {
                if((float) $this->mappedMarketsWithOutcomes['wdls'][0]['Price'] > (float) $this->mappedMarketsWithOutcomes['wdls'][2]['Price']) {
                    return 1;
                }
            }
        }

        return 0;
    }

    public function getIParam2() : int
    {
        $required = $this->isParamRequired($this->marketTemplate->market_type_id, $this->marketTemplate->market_type_count, static::I_PARAM_2);

        if($required) {
            if(isset($this->mappedMarketsWithOutcomes['wdls'])) {
                if((float) $this->mappedMarketsWithOutcomes['wdls'][0]['Price'] < (float) $this->mappedMarketsWithOutcomes['wdls'][2]['Price']) {
                    return 1;
                }
            }
        }

        return 0;
    }
}