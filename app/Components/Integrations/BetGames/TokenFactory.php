<?php

namespace App\Components\Integrations\BetGames;

use Illuminate\Cache\Repository;

class TokenFactory
{
    private $userId;
    private $currency;
    private $expiration_time;
    const CACHE_KEY = 'token:';
    /**
     * @var Repository
     */
    private $cache;

    public function __construct($userId, $currency)
    {
        $this->userId = $userId;
        $this->currency = $currency;
        $this->expiration_time = config('integrations.betGames.token_expiration_time');
    }

    public function create()
    {
        $time = time();
        $token = implode('-', [
            'secret' => config('integrations.betGames.secret'),
            'user_id' => $this->userId,
            'currency' => $this->currency,
            'time' => $time,
        ]);
        $this->cache->put($token, $time, $this->expiration_time);

        return new Token($token);
    }
}