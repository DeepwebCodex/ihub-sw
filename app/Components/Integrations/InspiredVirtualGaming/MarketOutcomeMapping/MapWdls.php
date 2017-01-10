<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping;


class MapWdls extends BaseMap implements MarketOutcomeMapInterface
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
}