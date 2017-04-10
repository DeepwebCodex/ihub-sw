<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class WirexGamingProdObjectIdMap
 * @package App\Models
 */
class WirexGamingProdObjectIdMap extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration';

    /**
     * {@inheritdoc}
     */
    protected $table = 'wirexgaming_prod_object_id_map';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'currency',
        'game_id'
    ];

    /**
     * @param int $user_id
     * @param string $currency
     * @param int $game_id
     * @return int
     */
    public static function getObjectId(int $user_id, string $currency, int $game_id): int
    {
        $model = static::where([
            ['game_id', $game_id],
            ['user_id', $user_id],
            ['currency', $currency]
        ])->first();

        if (!$model) {
            $model = static::create([
                'user_id' => $user_id,
                'currency' => $currency,
                'game_id' => $game_id
            ]);
        }
        return $model->id ?? 0;
    }

    /**
     * @return int
     */
    public static function getNextPrimaryIndex(): int
    {
        $mapModel = new static();

        $connection = $mapModel->getConnectionName();

        $table = $mapModel->getTable();

        $sequence_name = $table . '_id_seq';

        $value = DB::connection($connection)->select("SELECT nextval('{$sequence_name}')");

        return (int)$value['0']->nextval;
    }
}
