<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Exceptions\Api\ApiHttpException;
use App\Models\VirtualBoxing\EventLink as EventLinkModel;

/**
 * Class EventLink
 * @package App\Components\Integrations\VirtualBoxing
 */
class EventLink
{
    /**
     * @var EventLinkModel
     */
    protected $eventLinkModel;

    /**
     * @param $scheduleId
     * @param $eventId
     * @return void
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function create($scheduleId, $eventId):void
    {
        $eventLinkModel = new EventLinkModel([
            'event_vb_id' => (string)$scheduleId,
            'event_id' => $eventId
        ]);
        if (!$eventLinkModel->save()) {
            throw new ApiHttpException(400, 'cant_insert_link');
        }
        $this->eventLinkModel = $eventLinkModel;
    }

    /**
     * @param int $eventVbId
     * @return bool
     */
    public static function checkExistsByVbId($eventVbId):bool
    {
        return (bool)EventLinkModel::getByVbId($eventVbId);
    }
}
