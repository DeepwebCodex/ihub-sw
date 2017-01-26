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

class TennisDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'MeetingName');
    }

    public function getTournamentName(): string
    {
        return array_get($this->eventData, 'MeetingName');
    }

    public function getParticipants(): array
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

    public function getMappedResults(): array
    {
        $data = [];

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

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
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
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
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
    }
}