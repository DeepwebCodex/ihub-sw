<?php

namespace App\Components\Integrations;

use App\Components\Integrations\GameProviders\GameProviderInterface;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Internal\GameNotFoundException;
use App\Exceptions\Internal\GameProviderNotFoundException;
use App\Models\Erlybet\Cms\Error;

/**
 * Class CasinoGameLauncher
 * @package App\Components\ExternalServices
 */
class CasinoGameLauncher
{
    const ERROR_CODES = [
        'validation_error' => 1,
        'invalid_game' => 2,
        'invalid_user' => 3,
        'game_not_found' => 1,
    ];

    /**
     * @param $userId
     * @param array $game
     * @param string $lang
     * @param bool $isMobile
     * @param bool $isDemo
     * @return CasinoGameLauncherResult
     */
    public function launchGame($userId, $game, $lang, $isMobile, $isDemo):CasinoGameLauncherResult
    {
        $partnerId = (int)request()->server('PARTNER_ID');
        if (!$game) {
            return $this->errorResultWithDescription($lang, 'games', self::ERROR_CODES['invalid_game'], $partnerId);
        }

        $providerName = strtolower($game['provider_name']);
        try {
            $provider = $this->getProvider($providerName, $game['id'], $lang, $isMobile);
        } catch (GameProviderNotFoundException $e) {
            return $this->errorResult('Config or model not exist');
        }

        if ($isDemo) {
            return $this->launchGameDemo($lang, $provider, $partnerId);
        }

        return $this->launchGameReal($userId, $lang, $partnerId, $providerName, $provider);
    }

    /**
     * @param string $providerName
     * @param $gameId
     * @param string $lang
     * @param bool $isMobile
     * @return GameProviderInterface
     * @throws \App\Exceptions\Internal\GameProviderNotFoundException
     */
    protected function getProvider($providerName, $gameId, $lang, $isMobile):GameProviderInterface
    {
        $providerClass = 'GameProviders\\' . ucfirst($providerName);

        if (!config('integrations.' . $providerName) || !class_exists($providerClass)) {
            throw new GameProviderNotFoundException();
        }

        return new $providerClass($gameId, $lang, $isMobile);
    }

    /**
     * @param $lang
     * @param $typeError
     * @param $codeError
     * @param $partnerId
     * @param string $mixDescription
     * @return CasinoGameLauncherResult
     */
    protected function errorResultWithDescription(
        $lang,
        $typeError,
        $codeError,
        $partnerId,
        $mixDescription = null
    ):CasinoGameLauncherResult
    {
        $errorDescription = $this->getErrorDescription($lang, $typeError, $codeError, $partnerId, $mixDescription);
        return $this->errorResult($errorDescription);
    }

    /**
     * @param string $lang
     * @param string $typeError
     * @param int $codeError
     * @param int $partnerId
     * @param string $mixDescription
     * @return mixed|string
     */
    protected function getErrorDescription($lang, $typeError, $codeError, $partnerId, $mixDescription = null)
    {
        $error = (new Error())->getError($lang, $typeError, $codeError, $partnerId);
        if (!$error) {
            return "Can't find error: {$typeError}, {$lang}, {$codeError}";
        }
        $errorDescription = $error->description;
        if ($mixDescription) {
            $errorDescription = str_replace('{mixa}', $mixDescription, $errorDescription);
        }
        return $errorDescription;
    }

    /**
     * @param string $url
     * @return CasinoGameLauncherResult
     */
    protected function successResult($url):CasinoGameLauncherResult
    {
        $result = new CasinoGameLauncherResult(true);
        $result->setUrl($url);
        return $result;
    }

    /**
     * @param string $message
     * @return CasinoGameLauncherResult
     */
    protected function errorResult($message):CasinoGameLauncherResult
    {
        $result = new CasinoGameLauncherResult(false);
        $result->setMessage($message);
        return $result;
    }

    /**
     * @param $lang
     * @param GameProviderInterface $provider
     * @param $partnerId
     * @return CasinoGameLauncherResult
     */
    protected function launchGameDemo($lang, $provider, $partnerId):CasinoGameLauncherResult
    {
        try {
            $gameUrl = $provider->getGameDemo();
        } catch (GameNotFoundException $e) {
            return $this->onGameNotFound($lang, $partnerId);
        }
        return $this->successResult($gameUrl);
    }

    /**
     * @param $userId
     * @param $lang
     * @param $partnerId
     * @param $providerName
     * @param GameProviderInterface $provider
     * @return CasinoGameLauncherResult
     */
    protected function launchGameReal($userId, $lang, $partnerId, $providerName, $provider):CasinoGameLauncherResult
    {
        if (!$userId) {
            return $this->errorResultWithDescription($lang, 'games', self::ERROR_CODES['invalid_user'], $partnerId);
        }

        $serviceId = config('integrations.' . $providerName . '.service_id');

        try {
            $user = IntegrationUser::get($userId, $serviceId, 'casino');
        } catch (ApiHttpException $e) {
            return $this->errorResultWithDescription($lang, 'games', self::ERROR_CODES['validation_error'], $partnerId);
        }

        try {
            $gameUrl = $provider->getGameReal($user->getAttributes(), $user->getActiveWallet());
        } catch (GameNotFoundException $e) {
            return $this->onGameNotFound($lang, $partnerId);
        }
        return $this->successResult($gameUrl);
    }

    /**
     * @param string $lang
     * @param $partnerId
     * @return CasinoGameLauncherResult
     */
    protected function onGameNotFound($lang, $partnerId):CasinoGameLauncherResult
    {
        return $this->errorResultWithDescription(
            $lang,
            'all',
            self::ERROR_CODES['game_not_found'],
            $partnerId,
            'game_not_found'
        );
    }
}
