<?php

namespace App\Models;

use App\Components\Transactions\TransactionRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MicroGamingObjectIdMap extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'microgaming_object_id_map';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'currency',
        'game_id',
        'repeat'
    ];

    public static function getObjectId(int $user_id, string $currency, int $game_id, int $repeat = 0)
    {
        $object_id = static::generateHash($user_id, $currency, $game_id, $repeat);

        /**@var static $model*/
        $model = static::find($object_id);

        if($repeat <= 3 && $model && $model->validateMapDuplicate($user_id, $currency, $game_id, $object_id)) {
            $repeat++;

            return static::getObjectId($user_id, $currency, $game_id, $repeat);
        }

        if(!$model) {
            $model = static::create([
                'id'        => $object_id,
                'user_id'   => $user_id,
                'currency'  => $currency,
                'game_id'   => $game_id,
                'repeat'    => $repeat
            ]);
        }

        return isset($model->id) ? $model->id : null;
    }

    protected function validateMapDuplicate(int $user_id, string $currency, int $game_id, int $object_id) : bool
    {
        return !($user_id == $this->user_id && $currency == $this->currency && $game_id == $this->game_id) && $this->id == $object_id;
    }

    public static function generateHash(int $user_id, string $currency, int $game_id, int $repeat = 0) : int
    {
        return hexdec( substr(md5($user_id.$currency.$game_id.$repeat),0,15) );
    }
}
