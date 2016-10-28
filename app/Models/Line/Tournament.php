<?php

namespace App\Models\Line;

/**
 * Class Tournament
 * @package App\Models\Line
 */
class Tournament extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'tournament';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param $name
     * @param $categoryId
     * @return bool
     */
    public static function findByNameForSport($name, $categoryId)
    {
        return static::where([
            'name' => $name,
            'category_id' => $categoryId
        ]);
    }
}
