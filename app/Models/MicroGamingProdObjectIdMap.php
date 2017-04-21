<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MicroGamingProdObjectIdMap extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'microgaming_prod_object_id_map';

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

    public static function getObjectId(int $user_id, string $currency, int $game_id) : int
    {
        $model = static::where([
            ['game_id', $game_id],
            ['user_id', $user_id],
            ['currency', $currency]
        ])->first();

        if(!$model) {
            $model = static::create([
                'user_id'   => $user_id,
                'currency'  => $currency,
                'game_id'   => $game_id
            ]);
        }

        return isset($model->id) ? $model->id : 0;
    }

    public static function getNextPrimaryIndex() : int
    {
        $mapModel = new static();

        $connection = $mapModel->getConnectionName();

        $table = $mapModel->getTable();

        $sequence_name = $table . '_id_seq';

        $value = DB::connection($connection)->select("SELECT nextval('{$sequence_name}')");

        /*microgaming_prod_object_id_map_id_seq
        microgaming_prod_object_id_map_id_seq*/

        return intval($value['0']->nextval);
    }
}
