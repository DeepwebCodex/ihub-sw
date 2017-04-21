<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:09 AM
 */

namespace App\Components\Integrations\Vermantia\SportMapping;

use App\Components\Integrations\VirtualSports\Interfaces\SportDataMapInterface;
use App\Components\Integrations\VirtualSports\BaseSportDataMap;
use App\Models\Line\Participant;

class PlatinumHoundsDataMap extends BaseSportDataMap implements SportDataMapInterface
{
    public function getEventName(): string
    {
        return array_get($this->eventData, 'Name');
    }

    public function getTournamentName(): string
    {
        return array_get($this->eventData, 'EventType');
    }

    public function getParticipants(): array
    {
        $participants = array_get($this->eventData, 'Entry', []);

        $data = [];

        if($participants && is_array($participants)) {
            foreach ($participants as $participant) {
                $data[] = [
                    'name' => array_get($participant, 'Name'),
                    'number' => array_get($participant, 'Draw'),
                    'type' => Participant::TYPE_ATHLETE,
                    'position' => array_get($participant, 'Finish', 0)
                ];
            }
        }

        return $data;
    }

    public function getMappedResults(): array
    {
        $participants = array_get($this->eventData, 'Entry', []);

        $data = [];

        if($participants && is_array($participants)) {
            foreach ($participants as $participant) {
                $data[] = [
                    'num' => array_get($participant, 'Draw')-1,
                    'amount' => array_get($participant, 'Finish', 0)
                ];
            }
        }

        return $data;
    }

    public function getTotalResult(array $results, array $participants): string
    {
        return array_get($this->eventData, 'Result');
    }

    public function getTotalResultForJson(array $results, array $participants): array
    {
        $participants = array_get($this->eventData, 'Entry', []);

        $data = [];

        if($participants && is_array($participants)) {
            foreach ($participants as $participant) {
                $data[] = [
                    'name' => array_get($participant, 'Name'),
                    'position' => array_get($participant, 'Finish', 0)
                ];
            }
        }

        return $data;
    }
}