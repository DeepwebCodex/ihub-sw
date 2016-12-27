<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
}
