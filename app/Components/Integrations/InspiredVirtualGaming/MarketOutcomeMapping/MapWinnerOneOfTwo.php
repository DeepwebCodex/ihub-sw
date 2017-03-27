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

class MapWinnerOneOfTwo extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        'Price12' => 551,
        'Price13' => 552,
        'Price14' => 553,
        'Price15' => 554,
        'Price16' => 555,
        'Price23' => 556,
        'Price24' => 557,
        'Price25' => 558,
        'Price26' => 559,
        'Price34' => 560,
        'Price35' => 561,
        'Price36' => 562,
        'Price45' => 563,
        'Price46' => 564,
        'Price56' => 565
    ];

}