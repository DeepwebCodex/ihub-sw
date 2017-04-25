<?php

namespace App\Components\Integrations\VirtualSports;

use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
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
                throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::FAILED_INIT_RESULT));
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
            'result_type_id' => data_get($resultTypes, '0.id')
        ]);
    }
}
