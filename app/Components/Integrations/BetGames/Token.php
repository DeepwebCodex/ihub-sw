<?php

namespace App\Components\Integrations\BetGames;

use Illuminate\Cache\Repository;

class Token
{
    private $expiration_time;
    const CACHE_KEY = 'token:';
    /**
     * @var Repository
     */
    private $cache;
    private $token;
    private $decomposed;

    public function __construct($value = '')
    {
        $this->cache = app('cache')->store('redis_bet_games');
        $this->expiration_time = config('integrations.betGames.token_expiration_time');
        $this->token = $value;
        $this->decompose();
    }

    /**
     * @return string
     */
    public function get():string
    {
        return $this->token;
    }

    public function refresh()
    {
        $this->cache->put($this->token, time(), $this->expiration_time);
    }

    public function setNew()
    {
        if ($this->cache->has($this->token)) {
            $this->cache->forget($this->token);
        }
        $data = $this->decomposed;
        $data['time'] = time();
        $newToken = implode('-', $data);
        $this->cache->put($newToken, $data['time'], $this->expiration_time);
        return new self($newToken);
    }

    public function getUserId()
    {
        return $this->decomposed['user_id'];
    }
    
    public function getTime()
    {
        return $this->cache->get($this->token);
    }

    public function isExpired():bool
    {
        return (time() - $this->cache->get($this->token)) > $this->expiration_time;
    }

    public function isWrongCurrency(string $currency):bool
    {
        return $currency != $this->decomposed['currency'];
    }

    /**
     * @param $userId
     * @param $currency
     * @return Token
     */
    public function create($userId, $currency):self
    {
        $time = time();
        $token = implode('-', [
            'secret' => config('integrations.betGames.secret'),
            'user_id' => $userId,
            'currency' => $currency,
            'time' => $time,
        ]);
        $this->cache->put($token, $time, $this->expiration_time);

        return new self($token);
    }

    private function decompose()
    {
        list($res['secret'], $res['user_id'], $res['currency'], $res['time']) = explode('-', $this->token);
        $this->decomposed = $res;
    }
}