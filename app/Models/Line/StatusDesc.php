<?php

namespace App\Models\Line;

/**
 * Class StatusDesc
 * @package App\Models\Line
 */
class StatusDesc extends BaseLineModel
{
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
}
