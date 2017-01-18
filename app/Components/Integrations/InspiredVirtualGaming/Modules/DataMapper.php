<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Modules;

use App\Components\Integrations\InspiredVirtualGaming\SportMapping\FootballDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\HorsesDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\NumbersDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\SportDataMapInterface;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\TennisDataMap;
use Stringy\StaticStringy as S;

class DataMapper
{
    protected $eventData;

    protected $eventType;

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

    public function __construct(array $eventData, $eventType)
    {
        $this->eventData = $eventData;

        $this->eventType = $eventType;

        $this->mapper = $this->getMapper();
    }

    protected function getMapper() : SportDataMapInterface
    {
        $mapperClass = array_get($this->mappingRegistry, $this->eventType);

        if(!$mapperClass) {
            throw new \RuntimeException("Unable to locate mapper for sport {$this->eventType}", 6667);
        }

        return new $mapperClass($this->eventData);
    }

    public function getEventTime()
    {
        return array_get($this->eventData, 'EventTime');
    }

    public function getEventId()
    {
        return array_get($this->eventData, 'EventId');
    }

    public function getEventName() : string
    {
        return $this->mapper->getEventName();
    }

    public function getParticipants() : array
    {
        return $this->mapper->getParticipants();
    }

    public function getMappedResults() : array
    {
        return $this->mapper->getMappedResults();
    }

    public function getTotalResult(array $results, array $participants) : string
    {
        return $this->mapper->getTotalResult($results, $participants);
    }

    public function getTotalResultForJson(array $results, array $participants) : array
    {
        return $this->mapper->getTotalResultForJson($results, $participants);
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