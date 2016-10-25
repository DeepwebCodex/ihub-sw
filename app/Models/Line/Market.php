<?php

namespace App\Models\Line;

/**
 * Class Market
 * @package App\Models\Line
 */
class Market extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'market';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param int $eventId
     * @param string $suspend
     * @return int
     */
    public function updateMarketEvent(int $eventId, $suspend = 'no')
    {
        return \DB::connection($this->connection)
            ->table($this->table)
            ->where('event_id', $eventId)
            ->update(['suspend' => $suspend]);
    }
}
