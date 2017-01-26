<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:09 AM
 */

namespace App\Components\Integrations\VirtualBox\SportMapping;


use App\Components\Integrations\VirtualSports\Interfaces\SportDataMapInterface;
use App\Components\Integrations\VirtualSports\BaseSportDataMap;
use App\Models\Line\Participant;

class BoxingDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'match.name');
    }

    public function getTournamentName(): string
    {
        return array_get($this->eventData, 'match.location');
    }

    public function getParticipants(): array
    {
        $participants = [
            [
                'name' => array_get($this->eventData, 'match.home'),
                'number' => 1,
                'type' => Participant::TYPE_ATHLETE
            ],
            [
                'name' => array_get($this->eventData, 'match.away'),
                'number' => 2,
                'type' => Participant::TYPE_ATHLETE
            ]
        ];

        return $participants;
    }

    public function getMappedResults(): array
    {
        $data = [];

        $rounds =  array_get($this->eventData, 'round', []);

        if($rounds) {
            foreach ($rounds as $round) {

                $resultTypeId = (int) config("integrations.virtualBoxing.rounds_map." . array_get($round, 'round'));

                foreach (array_get($round, 'participant') as $participant) {
                    if(array_get($participant, 'point') == 1) {
                        $game_scope_id = (int) config("integrations.virtualBoxing.scope_type.point");

                        $data[] = [
                            'round' => array_get($round, 'round'),
                            'type' => 'point', // for total result mapping
                            'amount' => 1,
                            'num' => array_get($participant, 'id') -1,
                            'ResultTypeId' => $resultTypeId,
                            'game_result_scope_id' => $game_scope_id
                        ];
                    }

                    if(array_get($participant, 'knockdown') == 1) {
                        $game_scope_id = (int) config("integrations.virtualBoxing.scope_type.knockdown");

                        $data[] = [
                            'round' => array_get($round, 'round'),
                            'type' => 'knockdown', // for total result mapping
                            'amount' => 1,
                            'num' => array_get($participant, 'id')-1,
                            'ResultTypeId' => $resultTypeId,
                            'game_result_scope_id' => $game_scope_id
                        ];
                    }
                }

                if(array_get($round, 'status') != 'Draw') {
                    $game_scope_id = (int) config("integrations.virtualBoxing.scope_type.winner");

                    $data[] = [
                        'type' => 'win', // for total result mapping
                        'amount' => 1,
                        'num' => array_get($round, 'status')-1,
                        'ResultTypeId' => $resultTypeId,
                        'game_result_scope_id' => $game_scope_id
                    ];
                }
            }
        }

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
        $resultsO = [];
        
        $total1 = 0;
        $total2 = 0;

        foreach ($results as $result) {

            if(isset($result['round'])) {
                if(!isset($resultsO[$result['round']])){
                    $resultsO[$result['round']] = [];
                }

                if(!isset($resultsO[$result['round']][$result['num']])){
                    $resultsO[$result['round']][0] = 0;
                    $resultsO[$result['round']][1] = 0;
                }

                $resultsO[$result['round']][$result['num']] += $result['amount'];
                
                if($result['num'] == 0) $total1++;
                if($result['num'] == 1) $total2++;
            }
        }

        $output = "{$total1}:{$total2} ( ";

        $count = 1;
        foreach ($resultsO as $key => $res) {

            $output .= implode(':', $res);
            if($count != count($resultsO)) {
                $output .= ', ';
            }

            $count++;
        }

        $output .= ' )';

        return $output;
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
        return [];
    }

    public function getResultTypeId(int $default): int
    {
        return (int) config('integrations.virtualBoxing.rounds_map.6', $default);
    }
}