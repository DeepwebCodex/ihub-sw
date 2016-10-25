<?php

namespace App\Models\Line;

/**
 * Class Sport
 * @package App\Models\Line
 */
class Sport extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'sport';

    /**
     * @param int $sportId
     * @param int $eventId
     * @return bool
     */
    public function checkSportEventExists(int $sportId, int $eventId)
    {
        return \DB::connection('line')
            ->table($this->table .' AS s')
            ->select('s.id')
            ->join('category AS ca', 's.id', 'ca.sport_id')
            ->join('tournament AS t', 't.category_id', 'ca.id')
            ->join('event AS ev', 't.id', 'ev.tournament_id')
            ->where(
                [
                    'ev.id' => $eventId,
                    's.id' => $sportId,
                ]
            )
            ->exists();
    }
}
