<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NetEntertainmentObjectIdMap
 * @package App\Models
 */
class NetEntertainmentObjectIdMap extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration';

    /**
     * {@inheritdoc}
     */
    protected $table = 'netentertainment_object_id_map';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'game_id',
        'action_id',
    ];

    /**
     * @param int $gameId
     * @param string $actionId
     * @return int
     */
    public static function getObjectId(int $gameId, string $actionId): int
    {
        $model = static::where([
            ['game_id', $gameId],
            ['action_id', $actionId]
        ])->first();

        if (!$model) {
            $model = static::create([
                'game_id' => $gameId,
                'action_id' => $actionId,
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

        $sequenceName = $table . '_id_seq';

        $value = \DB::connection($connection)->select("SELECT nextval('{$sequenceName}')");

        return (int)$value['0']->nextval;
    }

    /**
     * @param $gameId
     * @return int
     */
    public static function findObjectIdByGameId($gameId)
    {
        $model = static::where([
            ['game_id', $gameId],
        ])->first();

        return $model->id ?? 0;
    }
}
