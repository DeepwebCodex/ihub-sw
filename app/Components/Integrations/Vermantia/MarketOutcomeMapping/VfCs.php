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

class VfCs extends BaseMarketOutcomeMapper implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        '0 - 0' => 281,
        '1 - 1' => 282,
        '2 - 2' => 283,
        '1 - 0' => 284,
        '2 - 0' => 285,
        '2 - 1' => 288,
        '3 - 0' => 286,
        '3 - 1' => 289,
        '4 - 0' => 287,
        '0 - 1' => 293,
        '0 - 2' => 294,
        '1 - 2' => 297,
        '0 - 3' => 295,
        '1 - 3' => 298,
        '0 - 4' => 296,
    ];
}