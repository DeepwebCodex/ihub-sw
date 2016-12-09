<?php

namespace App\Components\Integrations\GameSession;

/**
 * Class GameSession
 * @package App\Components
 */
class GameSessionService
{
    use ConfigResolver;

    /**
     * @var bool
     */
    private $sessionStarted = false;

    /**
     * @var SessionStoreItem
     */
    private $sessionStoreItem;

    /**
     * Create session
     *
     * @param array $sessionData
     * @return string
     * @throws \RuntimeException
     */
    public function create(array $sessionData):string
    {
        $referenceId = $this->makeReferenceId($sessionData);
        $referenceStoreItem = new ReferenceStoreItem($referenceId);
        $referenceStoreItem->read();
        $sessionId = $referenceStoreItem->getSessionId();

        if (SessionStoreItem::existsBySessionId($sessionId)) {
            $this->start($sessionId);
            return $sessionId;
        }

        $sessionId = $this->makeSessionId($sessionData);

        $this->sessionStoreItem = SessionStoreItem::create($sessionId, $sessionData, $referenceId);

        ReferenceStoreItem::create($referenceId, $sessionId);
        $this->sessionStarted = true;

        return $sessionId;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function makeReferenceId(array $data):string
    {
        $referenceKey = implode('', array_values($data));
        return hash_hmac('sha512', $referenceKey, $this->getConfigOption('storage_secret'));
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
     * Start an existing session
     *
     * @param string $sessionId
     * @throws \RuntimeException
     */
    public function start(string $sessionId)
    {
        $this->sessionStoreItem = new SessionStoreItem($sessionId);
        $this->sessionStoreItem->read();
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
        $sessionStoreItem = new SessionStoreItem($sessionId);
        $sessionStoreItem->checkExists()
            ->prolong();
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
        $sessionStoreItem = new SessionStoreItem($sessionId);
        $sessionStoreItem->read();
        $sessionData = $sessionStoreItem->getData();
        $referenceId = $sessionStoreItem->getReference();

        $newSessionId = $this->makeSessionId($sessionData);

        $sessionStoreItem->delete();

        $this->sessionStoreItem = SessionStoreItem::create($newSessionId, $sessionData, $referenceId);

        ReferenceStoreItem::create($referenceId, $newSessionId);

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
        return $this->sessionStoreItem->getDataField($key, $default);
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
     * Store value for session
     *
     * @param string $key
     * @param mixed $value
     * @throws \RuntimeException
     */
    public function store(string $key, $value)
    {
        $this->set($key, $value);
        $this->save();
    }

    /**
     * Set value for session
     *
     * @param string $key
     * @param mixed $value
     * @return array
     * @throws \RuntimeException
     */
    public function set(string $key, $value)
    {
        $this->validateSessionStarted();
        return $this->sessionStoreItem->setDataField($key, $value);
    }

    /**
     * Save session data to store
     *
     * @throws \RuntimeException
     */
    public function save()
    {
        $this->validateSessionStarted();
        $this->sessionStoreItem->save();
    }

    /**
     * Close current session
     */
    public function close()
    {
        $this->sessionStarted = false;
        unset($this->sessionStoreItem);
    }
}
