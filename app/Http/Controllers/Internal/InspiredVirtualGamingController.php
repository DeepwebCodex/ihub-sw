<?php

namespace App\Http\Controllers\Internal;

use App\Facades\AppLog;
use App\Http\Controllers\Controller;
use App\Models\Erlybet\CardItemModel;
use App\Models\Line\StatusDescModel;
use App\Repositories\FailedEventsRepository;

/**
 * Class InspiredVirtualGamingController
 * @package App\Http\Controllers\Internal
 */
class InspiredVirtualGamingController extends Controller
{
    const NODE = 'ivg_cancel';

    const IVG_SPORT_ID = 86;

    /**
     * @param int $limit
     * @param string $all
     * @param int $categoryId
     */
    public function cancelEvent($limit = 100, $all = 'part', $categoryId = null)
    {
        $eventIdList = (new FailedEventsRepository())
            ->getEventIdList($limit, $all, $categoryId, self::IVG_SPORT_ID);

        if ($eventIdList) {
            $closedEvents = [];
            $unclosedEvents = [];
            foreach ($eventIdList as $eventId) {
                if (CardItemModel::checkExistsByEventId($eventId) === false) {
                    StatusDescModel::create([
                        'status_type' => 'cancelled',
                        'name' => 'cancelled',
                        'event_id' => $eventId
                    ]);
                    $closedEvents[] = $eventId;
                } else {
                    $unclosedEvents[] = $eventId;
                }
            }
            $countUnclosedEvents = count($unclosedEvents);
            $countClosedEvents = count($closedEvents);

            $logMessage = ' Total: ' . ($countUnclosedEvents + $countClosedEvents)
                . ' Unclosed: ' . $countUnclosedEvents . ' ' . implode(', ', $unclosedEvents)
                . ' Was closed: ' . $countClosedEvents . ' ' . implode(', ', $closedEvents);

            AppLog::info($logMessage, self::NODE);
        }
    }
}
