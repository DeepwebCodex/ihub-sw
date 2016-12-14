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

    /**
     * {@inheritdoc}
     */
    public $fillable = ['event_market_id', 'event_participant_id', 'outcome_type_id', 'coef', 'dparam1'];
}
