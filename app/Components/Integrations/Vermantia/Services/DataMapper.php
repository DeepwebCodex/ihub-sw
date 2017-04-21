<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\Vermantia\Services;

use App\Components\Integrations\Vermantia\SportMapping\FootballDataMap;
use App\Components\Integrations\Vermantia\SportMapping\PlatinumHoundsDataMap;
use Carbon\Carbon;

class DataMapper extends \App\Components\Integrations\VirtualSports\Services\DataMapper
{
    protected $mappingRegistry = [
        'PlatinumHounds' => PlatinumHoundsDataMap::class,
        'Football'       => FootballDataMap::class
    ];

    public function getEventTime()
    {
        return $this->convertTimeToUtc('FinishTime')->format('Y-m-d H:i:s');
    }

    public function convertTimeToUtc(string $timeFiled) : Carbon
    {
        $timeLocal = new Carbon(array_get($this->eventData, $timeFiled));
        $diffHours  = array_get($this->eventData,'dateDiff', 0);

        return $timeLocal->addSeconds($diffHours);
    }

    public function getEventId()
    {
        return array_get($this->eventData, 'ID');
    }

    public function getMarketsWithOutcomes() : array
    {
        $data = [];

        $markets = array_get($this->eventData, 'Market');

        if($markets && is_array($markets)) {
            foreach ($markets as $market) {
                if(!isset($market['ClassCode'])) {
                    //RaceEvent
                    $marketName = data_get($market, 'ID');

                    $outcomes = data_get($market, 'Selection');

                    if ($outcomes && is_array($outcomes)) {
                        foreach ($outcomes as $outcome) {
                            $data[$marketName][] = [
                                'Price' => data_get($outcome, 'Odds'),
                                'Outcome' => $marketName,
                                'ParticipantRequire' => data_get($outcome, 'ID')
                            ];
                        }
                    }
                } else {
                    //FootballEvent
                    $marketName = data_get($market, 'ClassCode');

                    if(in_array($marketName, [
                        'VF-MR',
                        'VF-CS',
                        'VF-TG',
                        'VF-FT',
                        'VF-UO'
                    ])) {

                        $outcomes = data_get($market, 'Selection');

                        if ($outcomes && is_array($outcomes)) {
                            foreach ($outcomes as $outcome) {
                                $data[$marketName][] = [
                                    'Price' => data_get($outcome, 'OddsDecimal'),
                                    'Outcome' => data_get($outcome, 'Description'),
                                    'ResultTypeId' => 1
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}