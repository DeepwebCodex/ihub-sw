<?php

namespace App\Models\Line;

/**
 * Class StatusDescModel
 * @package App\Models\Line
 */
class StatusDescModel extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'status_desc';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;
}
