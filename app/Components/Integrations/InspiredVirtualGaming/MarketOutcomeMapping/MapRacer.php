<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping;

class MapRacer extends BaseMap implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        'Price' => 0,
        'PriceNotFirst' => 0,
        'PriceNotSecond' => 0,
        'PriceNotThird' => 0,
        'Show' => 0,
        'Place' => 0
    ];
}