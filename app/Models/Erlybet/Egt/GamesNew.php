<?php

namespace App\Models\Erlybet\Egt;

use App\Models\Erlybet\BaseErlybetModel;

/**
 * Class GamesNew
 * @package App\Models\Egt
 */
class GamesNew extends BaseErlybetModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'egt.games_new';

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
