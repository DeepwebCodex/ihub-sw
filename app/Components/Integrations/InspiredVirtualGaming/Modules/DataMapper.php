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

    public function getMappedResults() : array
    {
        $data = [];

        switch ($this->eventType)
        {
            case 4:
                $score = explode('-', array_get($this->eventData, 'Result', ''));
                if(!empty($score) && is_array($score)) {
                    foreach ($score as $num => $value)
                    {
                        for($i = 0; $i < $value; $i++) {
                            $data[] = [
                                'num'    => $num,
                                'amount' => 1
                            ];
                        }
                    }
                }
                break;
            case 5:
                foreach (array_get($this->eventData, 'number_drawn') as $number) {
                    $data[] = [
                        'num'    => array_get($number, 'Num'),
                        'amount' => array_get($number, 'Num')
                    ];
                }
                break;
            case 0:
            case 1:
            case 2:
            case 3:
            case 6:
            case 7:
                foreach (array_get($this->eventData, 'racer') as $racer) {
                    $data[] = [
                        'num'    => array_get($racer, 'Num'),
                        'amount' => array_get($racer, 'Position')
                    ];
                }
                break;
            case 8:
                foreach (array_get($this->eventData, 'point') as $point) {
                    $data[] = [
                        'num'    => array_get($point, 'player') +1,
                        'amount' => 1
                    ];
                }

                $total1 = 0;
                $total2 = 0;

                foreach($data as $result) {
                    if($result['num'] == 1)
                    {
                        $total1 += $result['amount'];
                    } else {
                        $total2 += $result['amount'];
                    }
                }

                if ($total1 > $total2) {
                    $data[] = [
                        'num'    => 1,
                        'amount' => 1,
                        'game_result_scope_id' => 6
                    ];
                    $data[] = [
                        'num'    => 2,
                        'amount' => 0,
                        'game_result_scope_id' => 6
                    ];
                } else {
                    $data[] = [
                        'num'    => 2,
                        'amount' => 1,
                        'game_result_scope_id' => 6
                    ];
                    $data[] = [
                        'num'    => 1,
                        'amount' => 0,
                        'game_result_scope_id' => 6
                    ];
                }

                break;
            default:
                break;
        }

        return $data;
    }

    public function getTotalResult(array $results, array $participants) : string
    {
        switch ($this->eventType)
        {
            case 4:
                $team1 = 0;
                $team2 = 0;
                foreach ($results as $result) {
                    if(array_get($result, 'num') == 0) {
                        $team1 ++;
                    } else {
                        $team2 ++;
                    }
                }

                return "{$team1} : {$team2} (0:0)";

                break;
            case 5:
                return "Finished";
            case 0:
            case 1:
            case 2:
            case 3:
            case 6:
            case 7:
                $output = '';
                $participants = collect($participants);
                foreach ($results as $result) {
                    $name = array_get($participants->where('number', $result['num'])->first() , 'name');
                    $output .= "{$result['amount']}pst - " . substr($name, 0, 5) . ".({$result['num']}), ";
                }
                return $output;
            case 8:

                $player1 = 0;
                $player2 = 0;

                foreach ($results as $result) {
                    if(!isset($result['game_result_scope_id'])) {
                        if($result['num'] == 1) {
                            $player1 += $result['amount'];
                        } else {
                            $player2 += $result['amount'];
                        }
                    }
                }

                if ($player1 > $player2) {
                    $player1 = "Win";
                    $player2 = "Lost";
                } else {
                    $player2 = "Win";
                    $player1 = "Lost";
                }

                return "{$player1} - {$player2}";
            default:
                return '';
        }
    }

    public function getTotalResultForJson(array $results, array $participants) : array
    {
        switch ($this->eventType)
        {
            case 4:
                $team1 = 0;
                $team2 = 0;
                foreach ($results as $result) {
                    if(array_get($result, 'num') == 0) {
                        $team1 ++;
                    } else {
                        $team2 ++;
                    }
                }

                return [
                    'team1' => $team1,
                    'team2' => $team2
                ];

                break;
            case 5:
                $collection = [];
                foreach ($results as $result) {
                    $collection[] = array_get($result, 'num');
                }

                return $collection;
            case 0:
            case 1:
            case 2:
            case 3:
            case 6:
            case 7:
                $output = [];
                $participants = collect($participants);
                foreach ($results as $result) {
                    $name = array_get($participants->where('number', $result['num'])->first() , 'name');

                    $output[$result['amount']] = [
                        'position' => $result['amount'],
                        'name'     => $name,
                        'num'      => $result['num']
                    ];
                }
                return $output;
            case 8:

                $player1 = 0;
                $player2 = 0;

                foreach ($results as $result) {
                    if(!isset($result['game_result_scope_id'])) {
                        if($result['num'] == 1) {
                            $player1 += $result['amount'];
                        } else {
                            $player2 += $result['amount'];
                        }
                    }
                }

                if ($player1 > $player2) {
                    $player1 = "Win";
                    $player2 = "Lost";
                } else {
                    $player2 = "Win";
                    $player1 = "Lost";
                }

                return [
                    'player1' => $player1,
                    'player2' => $player2
                ];
            default:
                return [];
        }
    }

    private function getParticipantsHorses() : array
    {
        $participants = [];

        foreach (array_get($this->eventData, 'racer') as $participant)
        {
            $participants[] = [
                'name'      => array_get($participant, 'Name'),
                'number'    => array_get($participant, 'Num'),
                'type'      => Participant::TYPE_ATHLETE,
                'position'  => array_get($participant, 'Position')
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