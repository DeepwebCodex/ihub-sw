<?php

namespace App\Models\Line;

/**
 * Class EventParticipant
 * @package App\Models\Line
 *
 * @property integer $id
 * @property integer $number
 */
class EventParticipant extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'event_participant';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public $fillable = ['number', 'participant_id', 'event_id', 'name'];
}
