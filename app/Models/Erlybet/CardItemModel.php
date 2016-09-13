<?php

namespace App\Models\Erlybet;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CardItemModel
 * @package App\Models\Erlybet
 */
class CardItemModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'erlybet_slave';

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
