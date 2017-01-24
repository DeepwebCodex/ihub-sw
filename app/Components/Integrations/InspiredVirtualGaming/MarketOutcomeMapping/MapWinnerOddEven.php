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

class MapWinnerOddEven extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'coefFiled' => 'Price',
        'outcomeFiled' => 'Outcome'
    ];

    protected $outcomeTypeMap = [
        'Odd' => 30,
        'Even' => 31
    ];
}