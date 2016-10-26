<?php

namespace App\Models\Line;

/**
 * Class Outcome
 * @package App\Models\Line
 */
class Outcome extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'outcome';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;
}
