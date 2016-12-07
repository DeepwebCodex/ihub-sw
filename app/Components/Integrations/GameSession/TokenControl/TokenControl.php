<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 12/7/16
 * Time: 11:20 AM
 */

namespace App\Components\Integrations\GameSession\TokenControl;


use Illuminate\Contracts\Cache\Repository;

class TokenControl
{
    /**
     * @var Repository
     */
    private $cacheStore;
    /**
     * @var string
     */
    private $integration;
    /**
     * @var int
     */
    private $lifetime;

    /**
     * @param Repository $cacheStore
     * @param string $integration
     * @param int $lifetime in minutes
     */
    public function __construct(string $integration, int $lifetime, $cacheStore = null)
    {
        $this->cacheStore = $cacheStore ? : app('cache')->store('redis_tokens');
        $this->integration = $integration;
        $this->lifetime = $lifetime;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function isUsed(string $token): bool
    {
        return !is_null($this->cacheStore->get($this->buildStoreKey($token)));
    }

    /**
     * @param string $token
     */
    public function setUsed(string $token)
    {
        $this->cacheStore->add($this->buildStoreKey($token), true, 20);
    }

    /**
     * @param string $token
     */
    public function register(string $token)
    {
        $this->cacheStore->add($this->buildStoreKey($token) . ':registered', time(), $this->lifetime + 1);
    }

    public function isExpired(string $token)
    {
        if($time = $this->cacheStore->get($this->buildStoreKey($token). ':registered'))
        {
            return (time() - $time) >= ($this->lifetime * 60);
        }

        return true;
    }

    protected function buildStoreKey(string $token)
    {
        return $this->integration . ':' . $token;
    }
}