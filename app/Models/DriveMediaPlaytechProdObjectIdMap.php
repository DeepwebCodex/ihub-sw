<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriveMediaPlaytechProdObjectIdMap extends Model
{
    protected $connection = 'integration';

    protected $table = 'drivemedia_playtech_prod_object_id_map';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'trade_id'
    ];

    public static function getObjectId(string $tradeId): int
    {
        $object_id = static::generateHash($tradeId);

        $model = static::find($object_id);

        if (!$model) {
            $model = static::create([
                'id' => $object_id,
                'trade_id' => $tradeId,
            ]);
        }

        return isset($model->id) ? $model->id : 0;
    }

    public static function generateHash(string $tradeId): int
    {
        return hexdec(substr(md5($tradeId), 0, 15));
    }

}