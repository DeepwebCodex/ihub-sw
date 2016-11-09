<?php

namespace App\Components\Integrations\GameSession;

/**
 * Class GameSession
 * @package App\Components
 */
class GameSessionService
{
    use SessionStore;

    const CONFIG_PREFIX = 'session.game_session';

    /**
     * @var bool
     */
    private $sessionStarted = false;

    /**
     * @var array
     */
    private $sessionData;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * Create session
     *
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
     * Make session id
     *
     * @param array $data
     * @return string
     */
    protected function makeSessionId(array $data):string
    {
        $sessionKey = implode('', array_values($data));
        $time = microtime(true);

        return hash_hmac('sha512', $sessionKey . $time, $this->getConfigOption('storage_secret'));
    }

    /**
     * @param $optionName
     * @return mixed
     */
    protected function getConfigOption($optionName)
    {
        return config(self::CONFIG_PREFIX . '.' . $optionName);
    }

    /**
     * Start an existing session
     *
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
     * Prolong session
     *
     * @param string $sessionId
     * @throws \RuntimeException
     */
    public function prolong(string $sessionId)
    {
        $this->checkSessionExistsStore($sessionId);
        $this->prolongSessionStore($sessionId);
    }

    /**
     * Regenerate session
     *
     * @param string $sessionId
     * @return string
     * @throws \RuntimeException
     */
    public function regenerate(string $sessionId):string
    {
        $this->readSessionDataStore($sessionId);

        $newSessionId = $this->makeSessionId($this->sessionData);

        $this->sessionId = $newSessionId;

        $this->deleteSessionStore($sessionId);
        $this->writeSessionDataStore();

        return $newSessionId;
    }

    /**
     * Get value for session
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws \RuntimeException
     */
    public function get(string $key, $default = null)
    {
        $this->validateSessionStarted();
        return array_get($this->sessionData, $key, $default);
    }

    /**
     * Validate session started
     *
     * @throws \RuntimeException
     */
    protected function validateSessionStarted()
    {
        if (!$this->isSessionStarted()) {
            throw new \RuntimeException('Session not started');
        }
    }

    /**
     * Check is session started
     *
     * @return boolean
     */
    public function isSessionStarted(): bool
    {
        return $this->sessionStarted;
    }

    /**
     * Set value for session
     *
     * @param string $key
     * @param mixed $value
     * @return array
     * @throws \Exception
     */
    public function set(string $key, $value)
    {
        $this->validateSessionStarted();
        return array_set($this->sessionData, $key, $value);
    }

    /**
     * Save session data to store
     *
     * @throws \RuntimeException
     */
    public function save()
    {
        $this->validateSessionStarted();
        $this->writeSessionDataStore();
    }
}
