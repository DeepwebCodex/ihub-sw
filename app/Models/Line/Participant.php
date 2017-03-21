<?php

namespace App\Models\Line;

/**
 * Class Participant
 * @package App\Models\Line
 */
class Participant extends BaseLineModel
{
    const TYPE_COACH = 'coach';
    const TYPE_ATHLETE = 'athlete';
    const TYPE_TEAM = 'team';
    const TYPE_UNDEFINED = 'undefined';
    const TYPE_OFFICIAL = 'official';
    const TYPE_ORGANIZATION = 'organization';

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


    public static function createOrUpdate(array $attributes = []) : Participant
    {
        /**@var Participant $model*/
        $model = static::where($attributes)->first();

        if(!$model){
            $model = static::create($attributes);
        } else {
            $model->fill($attributes);
            $model->save();
        }

        return $model;
    }
}
