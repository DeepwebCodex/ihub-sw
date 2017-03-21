<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\VirtualBox\Services;

use App\Components\Integrations\VirtualBox\SportMapping\BoxingDataMap;

class DataMapper extends \App\Components\Integrations\VirtualSports\Services\DataMapper
{
    protected $mappingRegistry = [
        'box' => BoxingDataMap::class
    ];

    public function getEventTime()
    {
        return (string) array_get($this->eventData, 'match.date', '') . " " . array_get($this->eventData, 'match.time', '');
    }

    public function getEventId()
    {
        return array_get($this->eventData, 'match.scheduleId');
    }

    public function getMarketsWithOutcomes() : array
    {
        $data = [];

        foreach (array_get($this->eventData, 'match.bet', []) as $bet) {

            if(is_array($bet)) {
                $market = array_get($bet, 'code');

                switch ($market) {
                    case 'R1':
                    case 'R2':
                    case 'R3':
                    case 'R4':
                    case 'R5':
                    case 'R6':
                        $round = (int) substr($market, -1);

                        foreach (array_get($bet, 'selection') as $selection){
                            $data['OW'][] = [
                                'Price' => array_get($selection, 'price.dec'),
                                'Outcome' => array_get($selection, 'home'),
                                'ResultTypeId' => config("integrations.virtualBoxing.rounds_map.{$round}")
                            ];
                        }
                        break;
                    case 'OW':
                        foreach (array_get($bet, 'selection') as $selection){
                            $data['OW'][] = [
                                'Price' => array_get($selection, 'price.dec'),
                                'Outcome' => array_get($selection, 'home'),
                            ];
                        }
                        break;
                    case 'CS1':
                    case 'CS2':
                    case 'CS3':
                    case 'CS4':
                    case 'CS5':
                    case 'CS6':
                        $round = (int) substr($market, -1);

                        foreach (array_get($bet, 'selection') as $selection){
                            $data['CSR'][] = [
                                'Price' => array_get($selection, 'price.dec'),
                                'Outcome' => array_get($selection, 'name'),
                                'ResultTypeId' => config("integrations.virtualBoxing.rounds_map.{$round}")
                            ];
                        }
                        break;
                    case 'KO4':
                        foreach (array_get($bet, 'selection') as $selection) {
                            $data[$market][] = [
                                'Price' => array_get($selection, 'price.dec'),
                                'Outcome' => array_get($selection, 'home')
                            ];
                        }
                        break;
                    default:
                        foreach (array_get($bet, 'selection') as $selection) {
                            $data[$market][] = [
                                'Price' => array_get($selection, 'price.dec'),
                                'Outcome' => array_get($selection, 'name')
                            ];
                        }
                        break;
                }
            }
        }

        return $data;
    }
}