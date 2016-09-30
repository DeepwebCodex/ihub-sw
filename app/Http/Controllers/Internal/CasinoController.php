<?php

namespace App\Http\Controllers\Internal;

use App\Components\Integrations\CasinoGameLauncherResult;
use App\Components\ExternalServices\Facades\RemoteSession;
use App\Http\Controllers\Controller;
use App\Repositories\CasinoGamesRepository;

/**
 * Class CasinoController
 * @package App\Http\Controllers\Internal
 */
class CasinoController extends Controller
{
    const NODE = 'casino';

    const GAME_INFO_FIELDS = [
        'game_name',
        'game_name_tr',
        'feature',
        'provider_name',
        'description_title'
    ];

    /**
     * @param string $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function allGameTypes($lang = 'ru')
    {
        $gameTypes = (new CasinoGamesRepository())->getAllGameTypes($lang);

        return $this->makeCommonSuccessResponse([
            'gametypes' => $gameTypes
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function allProviders()
    {
        $partnerId = (int)request()->server('PARTNER_ID');
        $gameProviders = (new CasinoGamesRepository())->getAllProviders($partnerId);

        return $this->makeCommonSuccessResponse([
            'providers' => $gameProviders
        ]);
    }

    /**
     * @param string $provider
     * @param string $gameType
     * @param string $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function allGames($provider = 'allproviders', $gameType = 'alltypes', $lang = 'ru')
    {
        $partnerId = (int)request()->server('PARTNER_ID');
        $games = (new CasinoGamesRepository())->getGames($provider, $gameType, $lang, $partnerId);

        return $this->makeCommonSuccessResponse([
            'games' => $games
        ]);
    }

    /**
     * @param string $typeEntity
     * @param string $entityName
     * @param string $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function allSeo($typeEntity = 'main', $entityName = 'alltypes', $lang = 'ru')
    {
        $seo = (new CasinoGamesRepository())->getSeo($typeEntity, $entityName, $lang);

        return $this->makeCommonSuccessResponse([
            'seo' => $seo
        ]);
    }

    /**
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function makeCommonSuccessResponse(array $data)
    {
        return response()->json(
            array_merge(
                [
                    'status' => true,
                    'message' => '',
                ],
                $data
            )
        );
    }

    /**
     * @param string $gameType
     * @param string $gameUrl
     * @param string $lang
     * @param bool $isMobile
     * @param bool $isDemo
     * @return \Illuminate\Http\JsonResponse
     */
    public function game($gameType = 'new', $gameUrl = '', $lang = 'ru', $isMobile = false, $isDemo = false)
    {
        $isMobile = filter_var($isMobile, FILTER_VALIDATE_BOOLEAN);
        $isDemo = filter_var($isDemo, FILTER_VALIDATE_BOOLEAN);

        $token = request()->cookie('PHPSESSID');
        $userId = RemoteSession::start($token)->get('user_id');

        $game = (new CasinoGamesRepository())->getGame($gameUrl, $lang);

        /** @var CasinoGameLauncherResult $gameLaunchResult */
        $gameLaunchResult = app('CasinoGameLauncher')->launchGame($userId, $game, $lang, $isMobile, $isDemo);

        $gameInfo = [];
        if ($game) {
            $gameInfo = array_only($game, self::GAME_INFO_FIELDS);
        }

        return response()->json(array_merge([
            'status' => $gameLaunchResult->getStatus(),
            'message' => $gameLaunchResult->getMessage(),
            'url' => $gameLaunchResult->getUrl(),
        ], $gameInfo));
    }
}
