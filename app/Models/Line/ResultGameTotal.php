<?php

namespace App\Models\Line;

/**
 * Class ResultGameTotal
 * @package App\Models\Line
 */
class ResultGameTotal extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'result_game_total';

    /**
     * @param $params
     * @param $eventId
     * @return bool
     */
    public static function updateResultGameTotal($params, $eventId)
    {
        return static::where('event_id', $eventId)
            ->update($params);
    }

    /**
     * @param $eventId
     * @param $str
     * @return mixed
     */
    public static function insertResultGameTotal($eventId, $str)
    {
        return static::create([
            'event_id' => $eventId,
            'result_total' => $str['result_total'],
            'result_total_json' => $str['result_total_json'],
            'result_type_id' => $str['result_type_id'],
        ]);
    }
}
