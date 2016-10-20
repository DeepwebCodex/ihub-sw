<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\VirtualBoxing;

class EventLinkModel extends BaseVirtualBoxingModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'event_link';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param int $event_vb_id
     * @return EventLinkModel|null
     */
    public static function getByVbId(int $event_vb_id = 0)
    {
        return EventLinkModel::where(
            ['event_vb_id' => $event_vb_id]
        )->first();
    }
}