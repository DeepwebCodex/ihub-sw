<?php

namespace App\Components\ExternalServices;

use App\Components\Users\IntegrationUser;
use App\Facades\AppLog;
use App\Models\Erlybet\Cms\Error;

/**
 * Class CasinoGameLauncher
 * @package App\Components\ExternalServices
 */
class CasinoGameLauncher
{
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
            return $this->errorResultWithDescription($lang, 'games', 2, $partnerId);
        }
        $providerName = strtolower($game['provider_name']);
        $path = APPPATH . 'config/' . $providerName . '.php';
        $modelName = $providerName;
        $pathModel = APPPATH . 'models/games/' . $modelName . '.php';

        if (!file_exists($path) || file_exists($pathModel)) {
            return $this->errorResult('Config or model not exist');
        }

        $this->load->model('games/' . $modelName, 'entity');

        if ($isDemo) {
            $res = $this->entity->getGameDemo($game['id'], $lang, $isMobile, $isDemo);
            if ($res) {
                return $this->successResult($this->entity->url);
            }
            return $this->errorResultWithDescription(
                $lang,
                $this->entity->type,
                $this->entity->code_error,
                $partnerId,
                $this->entity->mixa
            );
        }

        if (!$userId) {
            return $this->errorResultWithDescription($lang, 'games', 3, $partnerId);
        }

        $this->load->library('api');
        $this->load->config($providerName);

        $serviceId = $this->config->item('service_id');

        $user = IntegrationUser::get($userId, $serviceId, 'casino');

        $serviceInfo = $this->checkService($userId, $this->config->item('service_id'));

        $userActiveWallet = $user->getActiveWallet();

        if (!$serviceInfo || $userActiveWallet) {
            return $this->errorResultWithDescription($lang, 'games', 1, $partnerId);
        }

        $res = $this->entity->getGameReal(
            $game['id'],
            $lang,
            $isMobile,
            $isDemo,
            $userId,
            $userActiveWallet
        );
        if ($res) {
            return $this->successResult($this->entity->url);
        }
        return $this->errorResultWithDescription(
            $lang,
            $this->entity->type,
            $this->entity->code_error,
            $partnerId,
            $this->entity->mixa
        );
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

    /**
     * @param $userId
     * @param $serviceId
     * @return bool|string
     */
    protected function checkService($userId, $serviceId)
    {
        $this->CI->load->config('all');
        $this->CI->load->library('curl');
        $this->CI->curl->http_header('Content-Type', 'application/json');
        $data = json_encode(['id' => (int)$userId]);
        $user_t = json_decode(
            $this->CI->curl->simple_post(
                'http://' . $this->CI->config->item('account_roh_host')
                . ':' . $this->CI->config->item('account_roh_port')
                . '/accounts/account/get',
                $data
            ),
            true
        );
        if (isset($user_t['status']) && $user_t['status'] === 'ok') {
            $this->user_info = $user_t['response'];
            $user = $user_t['response'];
        } else {
            $codeError = $this->CI->curl->http_code;
            if ($codeError == 502 || $codeError == 504) {
                AppLog::warning("Repeat request (" . $codeError . ") Class " . __CLASS__ . " "
                    . print_r($data, true) . " " . print_r($_SERVER, true), 'repeat');
                http_response_code($codeError);
                die();
            }
            return ' Curl: ' . print_r($this->CI->curl->info, true);
        }

        $serviceInfo = $this->checkServiceMatch(
            $user['user_services'] ?? null,
            $serviceId
        );
        if (isset($user['user_services']) && $serviceInfo === true) {
            return $this->checkTestUser($userInfo);
        }
        return $serviceInfo . ' Answer: ' . print_r($user, true);
    }

    /**
     * @param $service
     * @param $serviceId
     * @return bool|string
     */
    protected function checkServiceMatch($service, $serviceId)
    {
        if (!$service || !is_array($service)) {
            return 'Service empty or bad structure';
        }
        foreach ($service as $value) {
            if (isset($value['service_id']) && $value['service_id'] === $serviceId) {
                if (!$value['is_enabled']) {
                    return 'Service is not enabled';
                }
                if ($value['is_blocked']) {
                    return 'Service is blocked';
                }
                return true;
            }
        }
        return 'Service not found';
    }

    /**
     * @return bool|string
     */
    protected function checkTestUser($userInfo)
    {
        $userGroup = (int)$userInfo['group'] ?? -1;
        if ($userGroup === 4
            && config('integrations.develop_only') === false
        ) {
            return 'This test player';
        }
        return true;
    }
}
