<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping;


class MapPlayerWinOutcomes extends BaseMap implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Num',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        '1' => 1,
        '2' => 3
    ];
}