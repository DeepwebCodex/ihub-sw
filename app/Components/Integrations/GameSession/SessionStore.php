<?php

namespace App\Components\Integrations\GameSession;

use Illuminate\Support\Facades\Redis;

/**
 * Class SessionStore
 * @package App\Components\Integrations\GameSession
 */
trait SessionStore
{
    /**
     * Write session data to store
     */
    protected function writeSessionDataStore()
    {
        $sessionData = $this->serialize($this->sessionData);
        Redis::setEx(
            $this->getStorageKey($this->sessionId),
            $this->getConfigOption('ttl'),
            $sessionData
        );
    }

    /**
     * Read session data from store
     *
     * @param string $sessionId
     * @throws \RuntimeException
     */
    protected function readSessionDataStore(string $sessionId)
    {
        $this->checkSessionExistsStore($sessionId);

        $sessionData = Redis::get($this->getStorageKey($sessionId));
        $this->prolongSessionStore($sessionId);
        $this->sessionData = $this->unserialize($sessionData);
    }

    /**
     * Check session data exists in store
     *
     * @param string $sessionId
     * @throws \RuntimeException
     */
    protected function checkSessionExistsStore(string $sessionId)
    {
        if (!Redis::exists($this->getStorageKey($sessionId))) {
            throw new \RuntimeException('Session does not exist');
        }
    }

    /**
     * Prolong session data store
     *
     * @param string $sessionId
     */
    protected function prolongSessionStore(string $sessionId)
    {
        Redis::expire($this->getStorageKey($sessionId), $this->getConfigOption('ttl'));
    }

    /**
     * Delete session data from store
     *
     * @param string $sessionId
     */
    protected function deleteSessionStore(string $sessionId)
    {
        Redis::del($this->getStorageKey($sessionId));
    }
}
