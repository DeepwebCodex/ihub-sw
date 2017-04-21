<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Collection;

/**
 * @property integer $participant_num
 * @property string $name
 * @property string $del
 * @property integer $weigh
 * @property string $name_format
 * @property integer $status_t
 * @property string  $last_update
 * @property integer $id
 *
 */
class OutcomeType extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'outcome_type';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public static function getOutcomeTypes(array $outcomeTypesIds)
    {
        /**@var Collection $templates*/
        $templates = static::whereIn('id', $outcomeTypesIds)->get();

        return  $templates->isNotEmpty() ? $templates : null;
    }
}
