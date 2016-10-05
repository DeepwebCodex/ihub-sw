<?php

namespace App\Components\Integrations\GameProviders;

use App\Exceptions\Internal\GameNotFoundException;

/**
 * Class BaseGameProvider
 * @package App\Components\Integrations\GameProviders
 */
abstract class BaseGameProvider
{
    protected $game;

    protected $lang;

    protected $isMobile;

    /**
     * @param $gameModel
     * @param $gameId
     * @return bool|mixed
     * @throws GameNotFoundException
     */
    protected function getGame($gameModel, $gameId)
    {
        $game = $gameModel->getById($gameId);
        if (!$game) {
            throw new GameNotFoundException();
        }
        return $game;
    }
}
