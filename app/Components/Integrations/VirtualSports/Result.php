<?php

namespace App\Components\Integrations\VirtualSports;

use App\Models\Line\Event as EventModel;
use App\Models\Line\ResultGame;
use App\Models\Line\ResultGameTotal;

/**
 * Class Result
 * @package App\Components\Integrations\VirtualSports
 */
class Result
{
    /**
     * @param $eventType
     * @param $eventId
     * @return void
     * @throws \RuntimeException
     */
    public function initResultEvent($eventType, $eventId)
    {
        $event = new EventModel();

        $validateValue = function ($value) {
            if (!$value) {
                throw new \RuntimeException('init_result');
            }
        };

        $scopeData = $event->preGetScope($eventId, $eventType);
        $validateValue($scopeData);

        $participants = $event->preGetParticipant($eventId);
        $validateValue($participants);

        $resultTypes = $event->preGetPeriodStart($eventId, $eventType);
        $validateValue($resultTypes);

        (new ResultGame)->checkResultTable($eventId, $resultTypes, $participants, $scopeData);

        ResultGameTotal::insertResultGameTotal($eventId, [
            'result_total' => '',
            'result_total_json' => '',
            'result_type_id' => $resultTypes[0]['id']
        ]);
    }
}
