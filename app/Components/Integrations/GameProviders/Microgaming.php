<?php

namespace App\Components\Integrations\GameProviders;

use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Exceptions\Internal\GameNotFoundException;
use App\Models\Erlybet\MIcrogaming\GamesNew;

/**
 * Class Microgaming
 * @package App\Components\Integrations\GameProviders
 */
class Microgaming implements GameProviderInterface
{
    protected $game;

    protected $lang;

    protected $isMobile;

    /**
     * Microgaming constructor.
     * @param $gameId
     * @param $lang
     * @param $isMobile
     * @throws \App\Exceptions\Internal\GameNotFoundException
     */
    public function __construct($gameId, $lang, $isMobile)
    {
        $this->game = $this->getGame($gameId);
        $this->lang = $lang === 'ru' ? 'ru' : 'en';
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
     * @param $userInfo
     * @param $walletInfo
     * @return string
     */
    public function getGameReal($userInfo, $walletInfo)
    {
        $token = MicroGamingHelper::generateToken(session_id(), $walletInfo['currency']);

        $isGameValid = ($this->game->applicationid && $this->game->productid);

        if ($this->isMobile) {
            if ($isGameValid) {
                return $this->getRealGameMobileUrl($token);
            }
            return $this->getRealGameMobileLobbyUrl($token);
        }
        if ($isGameValid) {
            return $this->getRealGameUrl($token);
        }
        return $this->getRealGameLobbyUrl($token);
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getRealGameMobileUrl($token):string
    {
        $queryData = [
            'authToken' => $token,
            'serverid' => config('integrations.microGaming.csid'),
            'applicationid' => $this->game['applicationid'],
            'moduleid' => $this->game['moduleid'],
            'clientid' => config('integrations.microGaming.client_id'),
            'productid' => $this->game['productid'],
            'ul' => $this->lang,
            'siteID' => 'MAL',
            'gameid' => $this->game['servergameid'],
            'playmode' => 'real'
        ];
        return config('integrations.microGaming.game_url') . http_build_query($queryData);
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getRealGameMobileLobbyUrl($token):string
    {
        $gameName = $this->game['servergameid'];

        $queryData = [
            'casinoID' => config('integrations.microGaming.csid'),
            'lobbyURL' => config('integrations.microGaming.game_mobile_lobby_url'),
            'bankingURL' => '',
            'loginType' => 'VanguardSessionToken',
            'authToken' => $token,
            'isRGI' => 'true',
        ];
        return config('integrations.microGaming.game_mobile_url') . "/{$gameName}/{$this->lang}"
        . http_build_query($queryData);
    }

    /**
     * @param $token
     * @return string
     */
    protected function getRealGameUrl($token):string
    {
        $queryData = [
            'authToken' => $token,
            'serverid' => config('integrations.microGaming.csid'),
            'applicationID' => $this->game['applicationid'],
            'ModuleID' => $this->game['moduleid'],
            'ClientID' => $this->game['clientid'],
            'ProductID' => $this->game['productid'],
            'ul' => $this->lang,
            'gameID' => $this->game['servergameid'],
            'playmode' => 'real'
        ];
        return config('integrations.microGaming.game_url') . http_build_query($queryData);
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getRealGameLobbyUrl($token):string
    {
        $queryData = [
            'bc' => "config-quickfiressl--{$this->lang}--MIT",
            'csid' => config('integrations.microGaming.csid'),
            'sext1' => 'genauth',
            'sext2' => 'genauth',
            'AuthToken' => $token,
            'gameid' => $this->game['servergameid'],
        ];
        return config('integrations.microGaming.game_lobby_url') . "{$this->lang}/" . http_build_query($queryData);
    }

    /**
     * @return string
     */
    public function getGameDemo()
    {
        $isGameValid = ($this->game->applicationid && $this->game->productid);

        if ($this->isMobile) {
            if ($isGameValid) {
                return $this->getDemoGameMobileUrl();
            }
            return $this->getDemoGameMobileLobbyUrl();
        }
        if ($isGameValid) {
            return $this->getDemoGameUrl();
        }
        return $this->getDemoGameLobbyUrl();
    }

    /**
     * @return string
     */
    protected function getDemoGameMobileUrl():string
    {
        $queryData = [
            'serverid' => config('integrations.microGaming.server_id'),
            'applicationid' => $this->game->applicationid,
            'moduleid' => $this->game->moduleid,
            'clientid' => config('integrations.microGaming.client_id'),
            'productid' => $this->game->productid,
            'ul' => $this->lang,
            'gameid' => $this->game->servergameid,
            'playmode' => 'demo',
        ];
        return config('integrations.microGaming.game_url') . http_build_query($queryData);
    }

    /**
     * @return string
     */
    protected function getDemoGameMobileLobbyUrl():string
    {
        $gameName = $this->game->servergameid;

        $queryData = [
            'loginType' => 'VanguardSessionToken',
            'isPracticePlay' => 'true',
            'casinoId' => config('integrations.microGaming.server_id'),
            'isRGI' => 'true',
            'authToken' => '',
            'lobbyurl' => config('integrations.microGaming.game_mobile_lobby_url'),
        ];
        return config('integrations.microGaming.game_mobile_url') . "/{$gameName}/{$this->lang}"
        . http_build_query($queryData);
    }

    /**
     * @return string
     */
    protected function getDemoGameUrl():string
    {
        $queryData = [
            'serverid' => config('integrations.microGaming.server_id'),
            'applicationID' => $this->game->applicationid,
            'ModuleID' => $this->game->moduleid,
            'ClientID' => config('integrations.microGaming.demo_game_client_id'),
            'ProductID' => $this->game->productid,
            'ul' => $this->lang,
            'gameID' => $this->game->servergameid,
        ];
        return config('integrations.microGaming.game_url') . http_build_query($queryData);
    }

    /**
     * @return string
     */
    protected function getDemoGameLobbyUrl():string
    {
        $queryData = [
            'sext1' => 'demo',
            'sext2' => 'demo',
            'bc' => "config-quickfiressl--{$this->lang}--MIT-Demo",
            'csid' => config('integrations.microGaming.server_id'),
            'gameid' => $this->game->servergameid,
        ];
        return config('integrations.microGaming.game_lobby_url') . "{$this->lang}/" . http_build_query($queryData);
    }
}
