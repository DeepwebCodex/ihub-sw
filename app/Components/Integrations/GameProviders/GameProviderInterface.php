<?php

namespace App\Components\Integrations\GameProviders;

/**
 * Interface GameProviderInterface
 * @package App\Components\Integrations\GameProviders
 */
interface GameProviderInterface
{
    /**
     * @param $userInfo
     * @param $walletInfo
     * @return mixed
     */
    public function getGameReal($userInfo, $walletInfo);

    /**
     * @return mixed
     */
    public function getGameDemo();
}
