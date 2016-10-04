<?php

namespace App\Components\Integrations\GameProviders;

use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Exceptions\Internal\GameNotFoundException;
use App\Models\Erlybet\Egt\GamesNew;

/**
 * Class Egt
 * @package App\Components\Integrations\GameProviders
 */
class Egt implements GameProviderInterface
{
    const PORTAL_CODE = 'Favbet_Seamless_';

    protected $game;

    protected $lang;

    protected $isMobile;

    /**
     * Egt constructor.
     * @param $gameId
     * @param $lang
     * @param $isMobile
     * @throws \App\Exceptions\Internal\GameNotFoundException
     */
    public function __construct($gameId, $lang, $isMobile)
    {
        $this->game = $this->getGame($gameId);
        $this->lang = $lang;
        $this->isMobile = $isMobile;
    }

    /**
     * @param $gameId
     * @return bool|mixed
     * @throws \App\Exceptions\Internal\GameNotFoundException
     */
    protected function getGame($gameId)
    {
        $game = (new GamesNew())->getById($gameId);
        if (!$game) {
            throw new GameNotFoundException();
        }
        return $game;
    }

    /**
     * @param array $userInfo
     * @param array $walletInfo
     * @return string
     */
    public function getGameReal($userInfo, $walletInfo):string
    {
        $userId = $userInfo['id'];
        $defenceCode = EgtHelper::generateDefenceCode($userId, $walletInfo['currency']);

        $queryData = [
            'defenceCode' => $defenceCode,
            'playerId' => $userId,
            'portalCode' => self::PORTAL_CODE . $walletInfo['currency'],
            'screenName' => $userInfo['fullname'],
            'language' => $this->lang,
            'country' => $userInfo['country_id'],
            'gameId' => $this->game->egt_gameid,
        ];
        if ($this->isMobile) {
            $queryData['client'] = 'mobile';
            $queryData['closeurl'] = '';
        }
        return config('integrations.egt.game_real_url') . http_build_query($queryData);
    }

    /**
     * @return string
     */
    public function getGameDemo():string
    {
        $queryData = [
            'gameId' => $this->game->egt_free_gameid
        ];
        return config('integrations.egt.game_demo_url') . http_build_query($queryData);
    }
}
