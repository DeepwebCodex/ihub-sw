<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\Vermantia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

/**
 * Class EventLink
 * @package App\Models\VirtualBoxing
 */
class EventLink extends Model
{
    const DB_SCHEMA = 'vermantia';

    /**
     * {@inheritdoc}
     */
    protected $connection = 'line';

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return self::DB_SCHEMA . '.' . parent::getTable();
    }

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
        return static::where('vermantia_event_link.event_id_vermantia', $vermantiaEventId)
            ->join('event', function($join){
                /** @var JoinClause $join */
                $join->on('vermantia_event_link.event_id', 'event.id')->where('event.del', 'no');
            })
            ->value('event_id');
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
