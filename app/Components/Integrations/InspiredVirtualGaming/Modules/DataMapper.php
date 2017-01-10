<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Modules;

use App\Models\Line\Participant;
use Stringy\StaticStringy as S;

class DataMapper
{
    protected $eventData;

    protected $eventType;

    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;

        $this->eventType = (int) array_get($this->eventData, 'EventType');
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
        switch ($this->eventType)
        {
            case 4:
                return array_get($this->eventData, 'EventName');
            case 8:
                return array_get($this->eventData, 'MeetingName');
            default:
                return array_get($this->eventData, 'CourseName');
        }
    }

    public function getParticipants() : array
    {
        switch ($this->eventType)
        {
            case 4:
                return $this->getParticipantsFootball();
            case 5:
                return $this->getParticipantsNumbers();
            case 8:
                return $this->getParticipantsTennis();
            default:
                return $this->getParticipantsHorses();
        }
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

    private function getParticipantsHorses() : array
    {
        $participants = [];

        foreach (array_get($this->eventData, 'racer') as $participant)
        {
            $participants[] = [
                'name' => array_get($participant, 'Name'),
                'number' => array_get($participant, 'Num'),
                'type' => Participant::TYPE_ATHLETE
            ];
        }

        return $participants;
    }

    private function getParticipantsFootball() : array
    {
        $participants = [
            [
                'name' => array_get($this->eventData, 'Team1'),
                'number' => 1,
                'type' => Participant::TYPE_TEAM
            ],
            [
                'name' => array_get($this->eventData, 'Team1'),
                'number' => 2,
                'type' => Participant::TYPE_TEAM
            ]
        ];

        return $participants;
    }

    private function getParticipantsNumbers() : array
    {
        $participants = [
            [
                'name' => 'Operators',
                'number' => 1,
                'type' => Participant::TYPE_TEAM
            ]
        ];

        return $participants;
    }

    private function getParticipantsTennis() : array
    {
        $participants = [
            [
                'name' => array_get($this->eventData, 'player.0.Name'),
                'number' => 1,
                'type' => Participant::TYPE_ATHLETE
            ],
            [
                'name' => array_get($this->eventData, 'player.1.Name'),
                'number' => 2,
                'type' => Participant::TYPE_ATHLETE
            ]
        ];

        return $participants;
    }

}