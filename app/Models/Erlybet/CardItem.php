<?php

namespace App\Models\Erlybet;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CardItem
 * @package App\Models\Erlybet
 */
class CardItem extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'erlybet_slave';

    /**
     * {@inheritdoc}
     */
    protected $table = 'carditem';

    /**
     * @param $eventId
     * @return mixed
     */
    public static function checkExistsByEventId($eventId)
    {
        return static::where('event', '=', $eventId)
            ->exists();
    }
}
