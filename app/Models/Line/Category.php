<?php

namespace App\Models\Line;

/**
 * Class Category
 * @package App\Models\Line
 */
class Category extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'category';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @param $name
     * @param $sportId
     * @return bool
     */
    public static function findByNameForSport($name, $sportId)
    {
        return static::where([
            'name' => $name,
            'sport_id' => $sportId
        ]);
    }
}
