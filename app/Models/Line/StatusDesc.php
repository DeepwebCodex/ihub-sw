<?php

namespace App\Models\Line;

/**
 * Class StatusDesc
 * @package App\Models\Line
 */
class StatusDesc extends BaseLineModel
{
    const STATUS_IN_PROGRESS = 'inprogress'; // is set when match is in progress and there is no more bets allowed
    const STATUS_INTERRUPTED = 'interrupted'; // hm not by us
    const STATUS_CANCELLED = 'cancelled'; // event has been cancelled
    const STATUS_NOT_STARTED = 'notstarted'; // event is not started
    const STATUS_FINISHED = 'finished'; // not set by as directly

    /**
     * {@inheritdoc}
     */
    protected $table = 'status_desc';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public $fillable = ['status_type', 'name', 'event_id'];

    /**
     * @see StatusDesc::STATUS_CANCELLED
     * @see StatusDesc::STATUS_IN_PROGRESS
     * @see StatusDesc::STATUS_NOT_STARTED
     *
     * @param string $statusName
     * @param int $eventId
     * @return StatusDesc
     */
    public static function createStatus(string $statusName, int $eventId) : StatusDesc
    {
        return StatusDesc::create([
            'status_type' => $statusName,
            'name' => $statusName,
            'event_id' => $eventId
            ]);
    }
}
