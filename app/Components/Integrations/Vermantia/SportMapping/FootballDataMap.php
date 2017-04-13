<?php

namespace App\Components\Integrations\Vermantia\SportMapping;

use App\Components\Integrations\VirtualSports\Interfaces\SportDataMapInterface;
use App\Components\Integrations\VirtualSports\BaseSportDataMap;
use App\Models\Line\Participant;
use Carbon\Carbon;

class FootballDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'HomeTeam') . ' - ' . array_get($this->eventData, 'AwayTeam');
    }

    public function getTournamentName(): string
    {
        return array_get($this->eventData, 'EventType') . ' - ' . array_get($this->eventData, 'Stadium');
    }

    public function getParticipants(): array
    {
        $data = [
            [
                'name' => array_get($this->eventData, 'HomeTeam'),
                'number' => 1,
                'type' => Participant::TYPE_TEAM,
            ],
            [
                'name' => array_get($this->eventData, 'AwayTeam'),
                'number' => 2,
                'type' => Participant::TYPE_TEAM,
            ]
        ];

        return $data;
    }

    public function getMappedResults(): array
    {
        /**Detect team to score first*/
        $market = collect(array_get($this->eventData, 'Market'))->where('ClassCode', '=', 'VF-FT')->first();

        $winningSection = collect($market)->get('WinningSelectionID');
        $team = data_get(collect(collect($market)->get('Selection'))->where('ID', '=', $winningSection)->first(), 'Description');

        $results = [
            [
                'num' => 0,
                'score' => array_get($this->eventData, 'Result.HomeScore')
            ],
            [
                'num' => 1,
                'score' => array_get($this->eventData, 'Result.AwayScore')
            ]
        ];

        switch ($team) {
            case 'HOME':
                break;
            case 'AWAY':
                $results = [
                    [
                        'num' => 1,
                        'score' => array_get($this->eventData, 'Result.AwayScore')
                    ],
                    [
                        'num' => 0,
                        'score' => array_get($this->eventData, 'Result.HomeScore')
                    ]
                ];
                break;
        }

        $data = [];

        foreach ($results as $result) {
            for($i = 0; $i < $result['score']; $i++) {
                $data[] = [
                    'num' => $result['num'],
                    'amount' => 1
                ];
            }
        }

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
        return array_get($this->eventData, 'Result.HomeScore'). ' : ' . array_get($this->eventData, 'Result.AwayScore') . ' (0:0)';
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
        $data = [
            'team1' => array_get($this->eventData, 'Result.HomeScore'),
            'team2' => array_get($this->eventData, 'Result.AwayScore')
        ];

        return $data;
    }
}