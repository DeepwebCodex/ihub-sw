<?php

namespace App\Components\Integrations\GameSession;

use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use Illuminate\Support\Facades\Redis;

/**
 * Class SessionStoreItem
 * @package App\Components\Integrations\GameSession
 */
class SessionStoreItem
{
    use Serializer, ConfigResolver;

    const SESSIONS_STORE_PREFIX = 'sessions';

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $referenceId = '';

    /**
     * @var string
     */
    protected $storageKey;

    /**
     * SessionStoreItem constructor.
     * @param string $sessionId
     */
    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->storageKey = $this->getStorageKeyPrefix() . ':' . self::SESSIONS_STORE_PREFIX . ':' . $sessionId;
    }

    /**
     * @param string $sessionId
     * @param array $sessionData
     * @param string $referenceId
     * @return self
     */
    public static function create($sessionId, $sessionData, $referenceId):self
    {
        $sessionStoreItem = new self($sessionId);
        $sessionStoreItem->setData($sessionData);
        $sessionStoreItem->setReferenceId($referenceId);
        return $sessionStoreItem->save();
    }

    /**
     * @param $sessionId
     * @return bool
     */
    public static function existsBySessionId($sessionId)
    {
        if (!$sessionId) {
            return false;
        }
        $sessionStoreItem = new self($sessionId);
        return $sessionStoreItem->exists();
    }

    /**
     * @return self
     */
    public function save():self
    {
        Redis::hmset(
            $this->storageKey,
            [
                'data' => $this->serialize($this->data),
                'reference' => $this->referenceId
            ]
        );
        $this->prolong();
        return $this;
    }

    /**
     * @return self
     */
    public function prolong():self
    {
        Redis::expire($this->storageKey, $this->getConfigOption('ttl'));
        return $this;
    }

    /**
     * @return array
     */
    public function getData():array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return self
     */
    public function setData(array $data):self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceId():string
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     * @return self
     */
    public function setReferenceId(string $referenceId):self
    {
        $this->referenceId = $referenceId;
        return $this;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getDataField(string $key, $default = null)
    {
        return array_get($this->data, $key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @return array
     */
    public function setDataField($key, $value)
    {
        return array_set($this->data, $key, $value);
    }

    /**
     * @return bool
     */
    public function exists():bool
    {
        return (bool)Redis::exists($this->storageKey);
    }

    /**
     * @return self
     * @throws \RuntimeException
     */
    public function read():self
    {
        $this->checkExists();
        $this->prolong();

        $data = Redis::hgetall($this->storageKey);

        $this->data = $this->unserialize($data['data']);
        $this->referenceId = $data['reference'];
        return $this;
    }

    /**
     * Check session data exists in store
     *
     * @return self
     * @throws \RuntimeException
     */
    public function checkExists():self
    {
        if (!$this->exists()) {
            throw new SessionDoesNotExist();
        }
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
}
