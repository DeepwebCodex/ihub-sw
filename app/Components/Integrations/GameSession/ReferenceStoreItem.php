<?php

namespace App\Components\Integrations\GameSession;

use Illuminate\Support\Facades\Redis;

/**
 * Class ReferenceStoreItem
 * @package App\Components\Integrations\GameSession
 */
class ReferenceStoreItem
{
    use Serializer, ConfigResolver;

    const REFERENCES_STORE_PREFIX = 'references';

    /**
     * @var string
     */
    protected $referenceId;

    /**
     * @var string
     */
    protected $sessionId = '';

    /**
     * @var string
     */
    protected $storageKey;

    /**
     * SessionReferenceStoreItem constructor.
     * @param string $referenceId
     */
    public function __construct(string $referenceId)
    {
        $this->referenceId = $referenceId;
        $this->storageKey = $this->getStorageKeyPrefix() . ':' . self::REFERENCES_STORE_PREFIX . ':' . $referenceId;
    }

    public static function getStorageKey($referenceId)
    {
        return \config('session.game_session.storage_key_prefix') . ':' . self::REFERENCES_STORE_PREFIX . ':' . $referenceId;
    }

    /**
     * @param string $referenceId
     * @param string $sessionId
     * @return ReferenceStoreItem
     */
    public static function create($referenceId, $sessionId):self
    {
        $referenceStoreItem = new self($referenceId);
        $referenceStoreItem->setSessionId($sessionId);
        return $referenceStoreItem->save();
    }

    /**
     * @return self
     */
    public function save():self
    {
        Redis::setex($this->storageKey, $this->getConfigOption('ttl'), $this->sessionId);
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return self
     */
    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return self
     */
    public function read():self
    {
        $this->sessionId = Redis::get($this->storageKey);
        return $this;
    }

    /**
     * @return self
     */
    public function delete():self
    {
        Redis::del($this->storageKey);
        return $this;
    }

    /**
     * @return self
     */
    public function prolong()
    {
        Redis::expire($this->storageKey, $this->getConfigOption('ttl'));
        return $this;
    }
}
