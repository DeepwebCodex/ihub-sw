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

class NumbersDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'CourseName');
    }

    public function getTournamentName(): string
    {
        return array_get($this->eventData, 'CourseName');
    }

    public function getParticipants(): array
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

    public function getMappedResults(): array
    {
        $data = [];

        foreach (array_get($this->eventData, 'number_drawn') as $number) {
            $data[] = [
                'num'    => array_get($number, 'Num'),
                'amount' => array_get($number, 'Num')
            ];
        }

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
        return "Finished";
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
        $collection = [];

        foreach ($results as $result) {
            $collection[] = array_get($result, 'num');
        }

        return $collection;
    }
}