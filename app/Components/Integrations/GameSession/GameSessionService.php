<?php

namespace App\Components\Integrations\GameSession;

use Illuminate\Support\Facades\Redis;

/**
 * Class GameSession
 * @package App\Components
 */
class GameSessionService
{
    use Serializer;

    const CONFIG_PREFIX = 'session.game_session';

    private $sessionStarted = false;

    private $sessionData;

    private $sessionId;

    /**
     * @param array $sessionData
     * @return string
     */
    public function create(array $sessionData):string
    {
        $sessionId = $this->makeSessionId($sessionData);

        $this->sessionId = $sessionId;
        $this->sessionData = $sessionData;

        $this->writeSessionDataStore();

        $this->sessionStarted = true;

        return $sessionId;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function makeSessionId(array $data):string
    {
        $sessionKey = implode('', array_values($data));
        $time = microtime(true);

        return hash_hmac('sha512', $sessionKey . $time, config(self::CONFIG_PREFIX . '.storage_secret'));
    }

    protected function writeSessionDataStore()
    {
        $sessionData = $this->serialize($this->sessionData);
        Redis::setEx(
            $this->getStorageKey($this->sessionId),
            config('session.game_session.ttl'),
            $sessionData
        );
    }

    /**
     * @param string $sessionId
     * @return string
     */
    protected function getStorageKey(string $sessionId)
    {
        return config(self::CONFIG_PREFIX . '.storage_key_prefix') . ':' . $sessionId;
    }

    /**
     * @param string $sessionId
     * @throws \RuntimeException
     */
    public function start(string $sessionId)
    {
        $this->readSessionDataStore($sessionId);

        $this->sessionId = $sessionId;
        $this->sessionStarted = true;
    }

    /**
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
     * @param string $sessionId
     */
    protected function prolongSessionStore(string $sessionId)
    {
        Redis::expire($this->getStorageKey($sessionId), config(self::CONFIG_PREFIX . '.ttl'));
    }

    /**
     * @param string $sessionId
     * @throws \RuntimeException
     */
    public function prolong(string $sessionId)
    {
        $this->checkSessionExistsStore($sessionId);
        $this->prolongSessionStore($sessionId);
    }

    /**
     * @param string $sessionId
     * @return string
     * @throws \RuntimeException
     */
    public function regenerate(string $sessionId):string
    {
        $this->readSessionDataStore($sessionId);

        $newSessionId = $this->makeSessionId($this->sessionData);

        $this->sessionId = $newSessionId;

        Redis::del($this->getStorageKey($sessionId));
        $this->writeSessionDataStore();

        return $newSessionId;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     * @throws \RuntimeException
     */
    public function get(string $key, $default = null)
    {
        $this->validateSessionStarted();
        return array_get($this->sessionData, $key, $default);
    }

    /**
     * @throws \RuntimeException
     */
    protected function validateSessionStarted()
    {
        if (!$this->isSessionStarted()) {
            throw new \RuntimeException('Session not started');
        }
    }

    /**
     * @return boolean
     */
    public function isSessionStarted(): bool
    {
        return $this->sessionStarted;
    }

    /**
     * @param string $key
     * @param $value
     * @return array
     * @throws \Exception
     */
    public function set(string $key, $value)
    {
        $this->validateSessionStarted();
        return array_set($this->sessionData, $key, $value);
    }

    public function save()
    {
        $this->validateSessionStarted();
        $this->writeSessionDataStore();
    }
}
