<?php

namespace App\Components\Integrations\BetGames;

use Illuminate\Cache\Repository;

class Token
{
    private $expiration_time;
    const CACHE_KEY = 'token:';
    const TIME_MULTIPLIER = 10000;
    /**
     * @var Repository
     */
    private $cache;
    private $token;
    private $decomposed;

    /**
     * Token constructor.
     */
    private function __construct()
    {
        $this->cache = app('cache')->store('redis_bet_games');
        $this->expiration_time = config('integrations.betGames.token_expiration_time');
    }

    /**
     * @param string $value
     * @return Token
     */
    public static function getByHash(string $value):self 
    {
        $object = new static();
        $object->initByHash($value);
        return $object;
    }

    /**
     * @param int $userId
     * @param string $currency
     * @return Token
     */
    public static function create(int $userId, string $currency):self 
    {
        $object = new static();
        $object->createNew($userId, $currency);
        return $object;
    }

    /**
     * @return string
     */
    public function get():string
    {
        return $this->token;
    }

    /**
     * @return Token
     */
    public function refresh():self 
    {
        $this->cache->put($this->token, $this->getCurrentTime(), $this->expiration_time);
        return $this;
    }

    /**
     * @return Token
     */
    public function getNew():self 
    {
        if ($this->cache->has($this->token)) {
            $this->cache->forget($this->token);
        }
        $data = $this->decomposed;
        $data['time'] = $this->getCurrentTime();
        $newToken = implode('-', $data);
        $this->cache->put($newToken, $data['time'], $this->expiration_time);

        return static::getByHash($newToken);
    }

    /**
     * @return int
     */
    public function getUserId():int 
    {
        return $this->decomposed['user_id'];
    }

    /**
     * @return string
     */
    public function getCurrency():string 
    {
        return $this->decomposed['currency'];
    }

    /**
     * @return int
     */
    public function getTime():int 
    {
        return $this->decomposed['time'];
    }

    /**
     * @return mixed
     */
    public function getCachedValue()
    {
        return $this->cache->get($this->token);
    }

    /**
     * @return bool
     */
    public function isExpired():bool
    {
        return ($this->getCurrentTime() - $this->cache->get($this->token)) / self::TIME_MULTIPLIER > $this->expiration_time;
    }


    /**
     * @return mixed
     */
    public function getCurrentTime()
    {
        return microtime(true) * self::TIME_MULTIPLIER;

    }

    /**
     * @param string $currency
     * @return bool
     */
    public function isWrongCurrency(string $currency):bool
    {
        return $currency != $this->decomposed['currency'];
    }

    private function decompose()
    {
        list($res['secret'], $res['user_id'], $res['currency'], $res['time']) = explode('-', $this->token);
        $this->decomposed = $res;
    }

    /**
     * @param string $value
     */
    private function initByHash(string $value)
    {
        $this->token = $value;
        $this->decompose();
    }

    /**
     * @param int $userId
     * @param string $currency
     */
    private function createNew(int $userId, string $currency)
    {
        $time = $this->getCurrentTime();
        $token = implode('-', [
            'secret' => config('integrations.betGames.secret'),
            'user_id' => $userId,
            'currency' => $currency,
            'time' => $time,
        ]);
        $this->cache->put($token, $time, $this->expiration_time);

        $this->initByHash($token);
    }
}