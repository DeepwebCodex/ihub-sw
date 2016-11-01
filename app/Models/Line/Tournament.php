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
     * {@inheritdoc}
     */
    public $fillable = [
        'tournament_name', 'weigh', 'enet_id', 'category_id', 'startdate', 'enddate', 'sportform_id', 'country_id',
        'enet_import', 'import_odds_provider,', 'max_bet', 'max_payout', 'stop_loss', 'margin', 'margin_prebet',
        'live_sportform_id', 'gender', 'user_id', 'sport_union_id', 'stop_loss_exp', 'max_bet_live',
        'max_payout_live', 'info_url'
    ];

    /**
     * @param string $name
     * @param int $categoryId
     * @return static|null
     */
    public static function findByNameForSport(string $name, int $categoryId)
    {
        return static::where([
            'name' => $name,
            'category_id' => $categoryId
        ])->first();
    }
}
