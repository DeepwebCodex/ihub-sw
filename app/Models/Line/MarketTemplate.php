<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Collection;

/**
 * @property
 * @property integer $id
 * @property string  $name
 * @property integer $market_type_id
 * @property integer $market_type_count
 * @property array  $outcome_types
 * @property integer $weigh
 */
class MarketTemplate extends BaseLineModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'market_template';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public function getOutcomeTypesAttribute($value)
    {
        return str_getcsv(trim($value, '{}'));
    }

    /**
     * {@inheritdoc}
     */
    public $fillable = ['outcome_types'];

    public static function getMarketTemplates(array $templateIds)
    {
        /**@var Collection $templates*/
        $templates = static::whereIn('id', $templateIds)->get();

        return  $templates->isNotEmpty() ? $templates : null;
    }
}
