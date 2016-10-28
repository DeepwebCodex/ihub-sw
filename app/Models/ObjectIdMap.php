<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;

class ObjectIdMap extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'object_id_map';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'object_id'
    ];

    /**
     * Creates or retrieves new mapping for partner object_id to internal unified mapping
     *
     * @param $object_id
     * @param int $service_id
     * @return mixed
     */
    public static function getObjectId($object_id, int $service_id){
        $model = self::where([
            ['object_id', $object_id],
            ['service_id', $service_id]
        ])->first();

        if(!$model){
            $model = self::create([
                'object_id' => $object_id,
                'service_id' => $service_id
            ]);
        }

        return isset($model->id) ? $model->id : null;
    }
}
