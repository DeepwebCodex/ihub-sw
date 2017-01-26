<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:09 AM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\SportMapping;

use App\Components\Integrations\VirtualSports\Interfaces\SportDataMapInterface;
use App\Components\Integrations\VirtualSports\BaseSportDataMap;
use App\Models\Line\Participant;

class FootballDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'Team1') . '-' . array_get($this->eventData, 'Team2');
    }

    public function getTournamentName(): string
    {
        return array_get($this->eventData, 'EventName');
    }

    public function getParticipants(): array
    {
        $participants = [
            [
                'name' => array_get($this->eventData, 'Team1'),
                'number' => 1,
                'type' => Participant::TYPE_TEAM
            ],
            [
                'name' => array_get($this->eventData, 'Team2'),
                'number' => 2,
                'type' => Participant::TYPE_TEAM
            ]
        ];

        return $participants;
    }

    public function getMappedResults(): array
    {
        $data = [];

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

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
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
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
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
    }
}