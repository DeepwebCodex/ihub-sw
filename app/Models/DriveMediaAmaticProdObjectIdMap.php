<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;

class DriveMediaAmaticProdObjectIdMap extends Model
{

    protected $connection = 'integration';

    protected $table = 'drivemedia_amatic_prod_object_id_map';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'trade_id'
    ];

    public static function getObjectId(string $trade_id) : int
    {
        $object_id = static::generateHash($trade_id);

        $model = static::find($object_id);

        if(!$model)
        {
            $model = static::create([
                'id' => $object_id,
                'trade_id' => $trade_id,
            ]);
        }

        return isset($model->id) ? $model->id : 0;
    }

    public static function generateHash(string $trade_id):int
    {
        return hexdec(substr(md5($trade_id), 0, 15));
    }

}