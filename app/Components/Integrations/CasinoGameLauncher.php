<?php

namespace App\Components\Integrations;

use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
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
    ];

    /**
     * @param $userId
     * @param array $game
     * @param string $lang
     * @param bool $isMobile
     * @param bool $isDemo
     * @return CasinoGameLauncherResult
     */
    public function launchGame($userId, $game, $lang, $isMobile, $isDemo)
    {
        $partnerId = (int)request()->server('PARTNER_ID');
        if (!$game) {
            return $this->errorResultWithDescription($lang, 'games', self::ERROR_CODES['invalid_game'], $partnerId);
        }

        $providerName = strtolower($game['provider_name']);

        if (!config('integrations.' . $providerName) || !class_exists($providerName)) {
            return $this->errorResult('Config or model not exist');
        }

        $provider = new $providerName();

        if ($isDemo) {
            $res = $provider->getGameDemo($game['id'], $lang, $isMobile, $isDemo);
            if ($res) {
                return $this->successResult($res->getUrl());
            }
            return $this->errorResultWithDescription($lang, $res->getType(), $res->getCodeError(), $partnerId, $res->getMixa());
        }

        if (!$userId) {
            return $this->errorResultWithDescription($lang, 'games', self::ERROR_CODES['invalid_user'], $partnerId);
        }

        $serviceId = config('integrations.' . $providerName . '.service_id');

        try {
            $user = IntegrationUser::get($userId, $serviceId, 'casino');
        } catch (ApiHttpException $e) {
            return $this->errorResultWithDescription($lang, 'games', self::ERROR_CODES['validation_error'], $partnerId);
        }

        $res = $provider->getGameReal($game['id'], $lang, $isMobile, $isDemo, $userId, $user->getActiveWallet());
        if ($res) {
            return $this->successResult($res->getUrl());
        }
        return $this->errorResultWithDescription($lang, $res->getType(), $res->getCodeError(), $partnerId, $res->getMixa());
    }

    /**
     * @param string $lang
     * @param string $typeError
     * @param int $codeError
     * @param int $partnerId
     * @param string $mixa
     * @return mixed|string
     */
    private function getErrorDescription($lang, $typeError, $codeError, $partnerId, $mixa = '')
    {
        $error = (new Error())->getError($lang, $typeError, $codeError, $partnerId);
        if (!$error) {
            return "Can't find error: {$typeError}, {$lang}, {$codeError}";
        }
        $errorDescription = $error->description;
        if ($mixa) {
            $errorDescription = str_replace('{mixa}', $mixa, $errorDescription);
        }
        return $errorDescription;
    }

    /**
     * @param string $url
     * @return CasinoGameLauncherResult
     */
    private function successResult($url)
    {
        $result = new CasinoGameLauncherResult(true);
        $result->setUrl($url);
        return $result;
    }

    /**
     * @param string $message
     * @return CasinoGameLauncherResult
     */
    private function errorResult($message)
    {
        $result = new CasinoGameLauncherResult(false);
        $result->setMessage($message);
        return $result;
    }

    /**
     * @param $lang
     * @param $typeError
     * @param $codeError
     * @param $partnerId
     * @param string $mixa
     * @return CasinoGameLauncherResult
     */
    private function errorResultWithDescription($lang, $typeError, $codeError, $partnerId, $mixa = '')
    {
        $errorDescription = $this->getErrorDescription($lang, $typeError, $codeError, $partnerId, $mixa);
        return $this->errorResult($errorDescription);
    }
}
