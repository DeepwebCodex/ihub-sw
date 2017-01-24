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

class HorsesDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'CourseName');
    }

    public function getParticipants(): array
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

    public function getMappedResults(): array
    {
        $data = [];

        foreach (array_get($this->eventData, 'racer') as $racer) {
            $data[] = [
                'num'    => array_get($racer, 'Num'),
                'amount' => array_get($racer, 'Position')
            ];
        }

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
        $output = '';
        $participants = collect($participants);

        foreach ($results as $result) {
            $name = array_get($participants->where('number', $result['num'])->first() , 'name');
            $output .= "{$result['amount']}pst - " . substr($name, 0, 5) . ".({$result['num']}), ";
        }

        return $output;
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
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
    }
}