<?php

namespace App\Components\Integrations\GameProviders;

use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Models\Erlybet\Microgaming\GamesNew;

/**
 * Class Microgaming
 * @package App\Components\Integrations\GameProviders
 */
class Microgaming extends BaseGameProvider implements GameProviderInterface
{
    /**
     * Microgaming constructor.
     * @param $gameId
     * @param $lang
     * @param $isMobile
     * @throws \App\Exceptions\Internal\GameNotFoundException
     */
    public function __construct($gameId, $lang, $isMobile)
    {
        $this->game = $this->getGame(new GamesNew(), $gameId);
        $this->lang = $lang === 'ru' ? 'ru' : 'en';
        $this->isMobile = $isMobile;
    }

    /**
     * @param $userInfo
     * @param $walletInfo
     * @return string
     */
    public function getGameReal(array $userInfo, array $walletInfo):string
    {
        $token = MicroGamingHelper::generateToken(RemoteSession::getSessionId(), $walletInfo['currency']);

        $isGameSubProvider = $this->isGameSubProvider();

        if ($this->isMobile) {
            if ($isGameSubProvider) {
                return $this->getRealGameMobileUrl($token);
            }
            return $this->getRealGameMobileLobbyUrl($token);
        }
        if ($isGameSubProvider) {
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
            'serverid' => config('integrations.microgaming.csid'),
            'applicationid' => $this->game['applicationid'],
            'moduleid' => $this->game['moduleid'],
            'clientid' => config('integrations.microgaming.client_id'),
            'productid' => $this->game['productid'],
            'ul' => $this->lang,
            'siteID' => 'MAL',
            'gameid' => $this->game['servergameid'],
            'playmode' => 'real'
        ];
        return config('integrations.microgaming.game_url') . '?' . http_build_query($queryData);
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getRealGameMobileLobbyUrl($token):string
    {
        $gameName = $this->game['servergameid'];

        $queryData = [
            'casinoID' => config('integrations.microgaming.csid'),
            'lobbyURL' => config('integrations.microgaming.game_mobile_lobby_url'),
            'bankingURL' => '',
            'loginType' => 'VanguardSessionToken',
            'authToken' => $token,
            'isRGI' => 'true',
        ];
        return config('integrations.microgaming.game_mobile_url') . "/{$gameName}/{$this->lang}"
        . '?' . http_build_query($queryData);
    }

    /**
     * @param $token
     * @return string
     */
    protected function getRealGameUrl($token):string
    {
        $queryData = [
            'authToken' => $token,
            'serverid' => config('integrations.microgaming.csid'),
            'applicationID' => $this->game['applicationid'],
            'ModuleID' => $this->game['moduleid'],
            'ClientID' => $this->game['clientid'],
            'ProductID' => $this->game['productid'],
            'ul' => $this->lang,
            'gameID' => $this->game['servergameid'],
            'playmode' => 'real'
        ];
        return config('integrations.microgaming.game_url') . '?' . http_build_query($queryData);
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getRealGameLobbyUrl($token):string
    {
        $queryData = [
            'bc' => "config-quickfiressl--{$this->lang}--MIT",
            'csid' => config('integrations.microgaming.csid'),
            'sext1' => 'genauth',
            'sext2' => 'genauth',
            'AuthToken' => $token,
            'gameid' => $this->game['servergameid'],
        ];
        return config('integrations.microgaming.game_lobby_url') . "{$this->lang}/" . '?' . http_build_query($queryData);
    }

    /**
     * @return string
     */
    public function getGameDemo()
    {
        $isGameSubProvider = $this->isGameSubProvider();

        if ($this->isMobile) {
            if ($isGameSubProvider) {
                return $this->getDemoGameMobileUrl();
            }
            return $this->getDemoGameMobileLobbyUrl();
        }
        if ($isGameSubProvider) {
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
            'serverid' => config('integrations.microgaming.server_id'),
            'applicationid' => $this->game->applicationid,
            'moduleid' => $this->game->moduleid,
            'clientid' => config('integrations.microgaming.client_id'),
            'productid' => $this->game->productid,
            'ul' => $this->lang,
            'gameid' => $this->game->servergameid,
            'playmode' => 'demo',
        ];
        return config('integrations.microgaming.game_url') . '?' . http_build_query($queryData);
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
            'casinoId' => config('integrations.microgaming.server_id'),
            'isRGI' => 'true',
            'authToken' => '',
            'lobbyurl' => config('integrations.microgaming.game_mobile_lobby_url'),
        ];
        return config('integrations.microgaming.game_mobile_url') . "/{$gameName}/{$this->lang}"
        . '?' . http_build_query($queryData);
    }

    /**
     * @return string
     */
    protected function getDemoGameUrl():string
    {
        $queryData = [
            'serverid' => config('integrations.microgaming.server_id'),
            'applicationID' => $this->game->applicationid,
            'ModuleID' => $this->game->moduleid,
            'ClientID' => config('integrations.microgaming.demo_game_client_id'),
            'ProductID' => $this->game->productid,
            'ul' => $this->lang,
            'gameID' => $this->game->servergameid,
        ];
        return config('integrations.microgaming.game_url') . '?' . http_build_query($queryData);
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
            'csid' => config('integrations.microgaming.server_id'),
            'gameid' => $this->game->servergameid,
        ];
        return config('integrations.microgaming.game_lobby_url') . "{$this->lang}/" . '?' . http_build_query($queryData);
    }

    /**
     * @return bool
     */
    protected function isGameSubProvider():bool
    {
        return $this->game->applicationid && $this->game->productid;
    }
}
