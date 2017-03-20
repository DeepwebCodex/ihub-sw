<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NovomaticProdObjectIdMap
 * @package App\Models
 */
class DriveMediaNovomaticProdObjectIdMap extends Model
{
    protected $connection = 'integration';

    protected $table = 'drivemedia_novomatic_prod_object_id_map';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'trade_id'
    ];

    /**
     * @param string $tradeId
     * @return int
     */
    public static function getObjectId(string $tradeId): int
    {
        $model = static::where([
            ['trade_id', $tradeId]
        ])->first();
        if (!$model) {
            $model = static::create([
                'trade_id' => $tradeId
            ]);
        }
        return $model->id ?? 0;
    }
}
