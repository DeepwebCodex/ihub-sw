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

class MapScores extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        '2-1' => 288,
        '0-2' => 294,
        '1-2' => 297,
        '2-3' => 299,
        '2-2' => 283,
        '3-0' => 286,
        '3-2' => 290,
        '0-1' => 293,
        '1-3' => 298,
        '0-0' => 281,
        '1-0' => 284,
        '4-0' => 287,
        '0-3' => 295,
        '1-1' => 282,
        '2-0' => 285,
        '3-1' => 289,
        '0-4' => 296
    ];
}