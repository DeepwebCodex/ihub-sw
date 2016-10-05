<?php

namespace App\Models\Erlybet\MIcrogaming;

use App\Models\Erlybet\BaseErlybetModel;

/**
 * Class GamesNew
 * @package App\Models\Erlybet\MIcrogaming
 */
class GamesNew extends BaseErlybetModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'microgaming.games_new';

    /**
     * @param $gameId
     * @return mixed
     */
    public function getById($gameId)
    {
        return static::where('game_id', $gameId)
            ->first();
    }
}
