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
     * {@inheritdoc}
     */
    public $fillable = ['name', 'weigh', 'enet_id', 'sport_id', 'gender', 'country_id', 'slug'];

    /**
     * @param string $name
     * @param int $sportId
     * @return static|null
     */
    public static function findByNameForSport(string $name, int $sportId)
    {
        return static::where([
            'name' => $name,
            'sport_id' => $sportId
        ])->first();
    }
}
