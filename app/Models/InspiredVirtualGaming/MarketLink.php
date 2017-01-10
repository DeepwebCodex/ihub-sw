<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/3/16
 * Time: 5:55 PM
 */

namespace App\Models\InspiredVirtualGaming;

use Illuminate\Database\Query\JoinClause;

/**
 * Class EventLink
 * @package App\Models\VirtualBoxing
 */
class MarketLink extends BaseInspiredModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'market_link';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public static function getTemplates(int $eventType)
    {
        return static::whereRaw("{$eventType} = ANY (market_link.event_type)")
            ->join('market_template', function($join){
                /** @var JoinClause $join */
                $join->on('market_template.id', 'market_link.market_template_id');
            })->get()->all();
    }
}
