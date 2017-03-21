<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Services;

use App\Components\Integrations\InspiredVirtualGaming\SportMapping\FootballDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\HorsesDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\NumbersDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\TennisDataMap;
use Stringy\StaticStringy as S;

class DataMapper extends \App\Components\Integrations\VirtualSports\Services\DataMapper
{
    protected $mappingRegistry = [
        0 => HorsesDataMap::class,
        1 => HorsesDataMap::class,
        2 => HorsesDataMap::class,
        3 => HorsesDataMap::class,
        4 => FootballDataMap::class,
        5 => NumbersDataMap::class,
        6 => HorsesDataMap::class,
        7 => HorsesDataMap::class,
        8 => TennisDataMap::class
    ];

    public function getEventTime()
    {
        return array_get($this->eventData, 'EventTime');
    }

    public function getEventId()
    {
        return array_get($this->eventData, 'EventId');
    }

    public function getMarketsWithOutcomes() : array
    {
        $data = [];

        foreach ($this->eventData as $market => $marketData)
        {
            if(is_array($marketData))
            {
                foreach ($marketData as $name => $outcomesArray) {
                    if(is_array($outcomesArray)) {
                        foreach ($outcomesArray as $attr_name => $outcome) {
                            switch ($market)
                            {
                                case 'racer':
                                    if(!S::startsWith($attr_name, '@') && in_array($attr_name, ['Price', 'PriceNotFirst', 'PriceNotSecond', 'PriceNotThird', 'Show', 'Place'])) {
                                        $data[$market][] = [
                                            'Price' => $outcome,
                                            'Outcome' => $attr_name,
                                            'PayOut'  => array_get($outcomesArray, 'PayOut', 0)
                                        ];
                                    }
                                    break;
                                default:
                                    $data[$market][] = $outcome;
                            }
                        }
                    } else {
                        switch ($market)
                        {
                            case 'winnerOddEven':
                                if($name == 'PriceOdd') {
                                    $data[$market][] = [
                                        'Price' => $outcomesArray,
                                        'Outcome' => 'Odd'
                                    ];
                                } elseif($name == 'PriceEven') {
                                    $data[$market][] = [
                                        'Price' => $outcomesArray,
                                        'Outcome' => 'Even'
                                    ];
                                }
                                break;
                            case 'winnerYesNo':
                                if(!S::startsWith($name, '@')) {
                                    $data[$market][] = [
                                        'Price' => $outcomesArray,
                                        'Outcome' => $name
                                    ];
                                }
                                break;
                            case 'forecastData':
                            case 'winnerOneOfTwo':
                                if(!S::startsWith($name, '@')) {
                                    $data[$market][] = [
                                        'Price' => $outcomesArray,
                                        'Outcome' => $name
                                    ];
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        return $data;
    }
}