<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping;


class MapForecastData extends BaseMap implements MarketOutcomeMapInterface
{
    protected $outcomeConfig = [
        'outcomeFiled' => 'Outcome',
        'coefFiled' => 'Price'
    ];

    protected $outcomeTypeMap = [
        'Price12' => 521,
        'Price13' => 522,
        'Price14' => 523,
        'Price15' => 524,
        'Price16' => 525,
        'Price21' => 526,
        'Price23' => 527,
        'Price24' => 528,
        'Price25' => 529,
        'Price26' => 530,
        'Price31' => 531,
        'Price32' => 532,
        'Price34' => 533,
        'Price35' => 534,
        'Price36' => 535,
        'Price41' => 536,
        'Price42' => 537,
        'Price43' => 538,
        'Price45' => 539,
        'Price46' => 540,
        'Price51' => 541,
        'Price52' => 542,
        'Price53' => 543,
        'Price54' => 544,
        'Price56' => 545,
        'Price61' => 546,
        'Price62' => 547,
        'Price63' => 548,
        'Price64' => 549,
        'Price65' => 550
    ];

}