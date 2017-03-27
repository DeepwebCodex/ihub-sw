<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\InspiredVirtualGaming;

use Illuminate\Database\Query\JoinClause;

/**
 * Class EventLink
 * @package App\Models\VirtualBoxing
 */
class EventLink extends BaseInspiredModel
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
    public $fillable = [ 'event_id', 'event_id_ivg' ];

    public static function getEventId(int $ivgEventId)
    {
        return static::where('event_link.event_id_ivg', $ivgEventId)
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

    /**
     * @return mixed
     */
    public static function getLastId()
    {
        $eventLink = static::orderBy('event_id_ivg', 'desc')->first();
        return $eventLink ? $eventLink->event_id_ivg : 0;
    }
}
