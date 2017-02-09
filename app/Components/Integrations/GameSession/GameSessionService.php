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
     * @param $algorithm
     * @return string
     */
    public function create(array $sessionData, $algorithm = 'sha512'):string
    {
        $referenceId = $this->makeReferenceId($sessionData);
        $referenceStoreItem = new ReferenceStoreItem($referenceId);
        $referenceStoreItem->read();
        $sessionId = $referenceStoreItem->getSessionId();

        if (SessionStoreItem::existsBySessionId($sessionId)) {
            $this->start($sessionId);
            return $sessionId;
        }

        $sessionId = $this->makeSessionId($sessionData, $algorithm);

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

        $referenceId = $this->sessionStoreItem->getReferenceId();
        $this->prolongReferenceStore($referenceId);
    }

    /**
     * Prolong reference store item
     * @param $referenceId
     */
    protected function prolongReferenceStore($referenceId)
    {
        $referenceStoreItem = new ReferenceStoreItem($referenceId);
        $referenceStoreItem->prolong();
    }

    /**
     * Make session id
     *
     * @param array $data
     * @param string $algorithm
     * @return string
     */
    protected function makeSessionId(array $data, $algorithm = 'sha512'):string
    {
        $sessionKey = implode('', array_values($data));
        $time = microtime(true);
        return hash_hmac($algorithm, $sessionKey . $time, $this->getConfigOption('storage_secret'));
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
        $sessionStoreItem->read();

        $referenceId = $sessionStoreItem->getReferenceId();
        $this->prolongReferenceStore($referenceId);
    }

    /**
     * Regenerate session
     *
     * @param string $sessionId
     * @param string $algorithm
     * @return string
     * @throws \RuntimeException
     */
    public function regenerate(string $sessionId, $algorithm = 'sha512'):string
    {
        $sessionStoreItem = new SessionStoreItem($sessionId);
        $sessionStoreItem->read();
        $sessionData = $sessionStoreItem->getData();
        $referenceId = $sessionStoreItem->getReferenceId();

        $newSessionId = $this->makeSessionId($sessionData, $algorithm);

        $sessionStoreItem->delete();

        $referenceStoreItem = new ReferenceStoreItem($referenceId);
        $referenceStoreItem->delete();

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

        $referenceId = $this->sessionStoreItem->getReferenceId();
        $this->prolongReferenceStore($referenceId);
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
