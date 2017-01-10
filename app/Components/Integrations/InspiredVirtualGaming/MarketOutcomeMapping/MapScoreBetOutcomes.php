<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping;


class MapScoreBetOutcomes extends BaseMap implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        '0-Win'     => 582,
        '15-Win'    => 583,
        '30-Win'    => 584,
        '40-Win'    => 585,
        'Win-0'     => 586,
        'Win-15'    => 587,
        'Win-30'    => 588,
        'Win-40'    => 589
    ];
}