<?php

namespace App\Components\Integrations\EuroGamesTech;

/**
 * Class Jackpot
 * @package App\Components\Integrations\EuroGamesTech
 */
class Jackpot
{
    const EXPIRATION_TIME = 5;
    const CACHE_KEY = 'jackpot';
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;
    /**
     * @var JackpotReceiver
     */
    private $receiver;

    public function __construct()
    {
        $this->cache = app('cache')->store('redis_egt');
        $this->receiver = new JackpotReceiver();
    }

    public function get()
    {
        if (!$this->cache->has(self::CACHE_KEY)) {
            $this->set();
        }
        return $this->cache->get(self::CACHE_KEY);
    }

    public function set()
    {
        $response = $this->receiver->request();
        if ($response) {
            $this->cache->put(self::CACHE_KEY, $response, self::EXPIRATION_TIME);
        }
    }
}