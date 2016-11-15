<?php

namespace App\Components\Integrations\GameSession;

/**
 * Trait ConfigResolver
 * @package App\Components\Integrations\GameSession
 */
trait ConfigResolver
{
    /**
     * @param string $optionName
     * @return mixed
     */
    protected function getConfigOption($optionName)
    {
        $configPrefix = 'session.game_session';
        return config($configPrefix . '.' . $optionName);
    }

    /**
     * @return mixed
     */
    protected function getStorageKeyPrefix()
    {
        return $this->getConfigOption('storage_key_prefix');
    }
}
