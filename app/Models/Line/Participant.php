<?php

namespace App\Models\Line;

/**
 * Class Participant
 * @package App\Models\Line
 */
class Participant extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'participant';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public $fillable = ['name', 'type', 'country_id', 'sport_id'];
}
