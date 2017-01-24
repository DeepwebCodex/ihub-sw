<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\VirtualBoxing;

/**
 * Class EventLink
 * @package App\Models\VirtualBoxing
 */
class EventLink extends BaseVirtualBoxingModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'event_link';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public $fillable = ['event_vb_id', 'event_id'];

    /**
     * @param int $eventVbId
     * @return EventLink|null
     */
    public static function getByVbId(int $eventVbId)
    {
        return static::where('event_vb_id', $eventVbId)
            ->first();
    }

    /**
     * @return mixed
     */
    public static function getLastVbId()
    {
        return static::orderBy('event_vb_id', 'desc')->first()->event_vb_id;
    }

    public static function getEventId(int $vbEventId)
    {
        return static::where('event_link.event_vb_id', $vbEventId)
            ->join('event', function($join){
                /** @var JoinClause $join */
                $join->on('event_link.event_id', 'event.id')->where('event.del', 'no');
            })
            ->value('event_id');
    }

    public static function isExists(int $ivgEventId) : bool
    {
        return (bool) static::getEventId($ivgEventId);
    }
}
