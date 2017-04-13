<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\Vermantia;

use App\Models\Line\Event;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventLink
 * @package App\Models\VirtualBoxing
 */
class EventLink extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration';

    /**
     * {@inheritdoc}
     */
    protected $table = 'vermantia_event_link';

    protected $primaryKey = 'event_id_vermantia';

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
    public $fillable = [ 'event_id', 'event_id_vermantia' ];

    public static function getEventId(int $vermantiaEventId)
    {
        $map = static::find($vermantiaEventId);

        if($map) {
            $event = Event::where([
                'id' => $map->event_id,
                'del' => 'no'
            ])->first();

            if($event) {
                return $event->id;
            }

            return true;
        }

        return false;
    }

    public static function isExists(int $eventId) : bool
    {
        return (bool) static::getEventId($eventId);
    }

    /**
     * @return mixed
     */
    public static function getLastId()
    {
        $eventLink = static::orderBy('event_id_vermantia', 'desc')->first();
        return $eventLink ? $eventLink->event_id_ivg : 0;
    }
}
