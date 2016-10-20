<?php

namespace App\Models\Line;

/**
 * Class StatusDescModel
 * @package App\Models\Line
 */
class EventModel extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'event';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;
}
