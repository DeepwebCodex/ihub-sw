<?php

namespace App\Components\Integrations\EuroGamesTech;

class DefenceCode
{
    const EXPIRATION_TIME = 2;
    const CACHE_KEY = 'defence_code:';

    /**
     * @var \Illuminate\Cache\Repository
     */
    private $cache;

    public function __construct()
    {
        $this->cache = app('cache')->store('redis_egt');
    }

    /**
     * @param int $userId
     * @param string $currency
     * @param null $time
     * @return string
     */
    public function generate(int $userId, string $currency, $time = null):string
    {
        if (!$time) {
            $time = time();
        }

        return md5($userId . $currency . config('integrations.egt.secret') . $time) . '-' . $time;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isUsed(string $code):bool
    {
        return !is_null($this->cache->get(self::CACHE_KEY . $code));
    }

    /**
     * @param string $code
     */
    public function setUsed(string $code)
    {
        $this->cache->add(self::CACHE_KEY . $code, true, self::EXPIRATION_TIME);
    }

    /**
     * @param $value
     * @param $userId
     * @param $currency
     * @return bool
     */
    public function isCorrect(string $value, int $userId, string $currency):bool
    {
        return $value == $this->generate($userId, $currency, $this->getTime($value));
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isExpired(string $code):bool
    {
        return (time() - $this->getTime($code)) > self::EXPIRATION_TIME;
    }

    /**
     * @param $value
     * @return mixed
     */
    private function getTime($value)
    {
        list($hash, $time) = explode('-', $value);
        return $time;
    }
}