<?php

namespace App\Models\Line;

/**
 * Class Sportform
 * @package App\Models\Line
 */
class Sportform extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'sportform';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param int $sportId
     */
    public static function findById(int $sportId)
    {
        return static::where('sport_id', $sportId)
            ->get()
            ->all();
    }
}
