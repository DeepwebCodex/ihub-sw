<?php

namespace App\Components\Integrations\BetGames;

use App\Components\Traits\MetaDataTrait;

class Signature
{
    use MetaDataTrait;

    const EXPIRATION_TIME = 1;
    const CACHE_KEY = 'signature:';

    /**
     * @var \Illuminate\Cache\Repository
     */
    private $cache;
    private $hash;

    /**
     * Signature constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->cache = app('cache')->store('redis_bet_games');
        $this->hash = $this->create($data);
    }


    /**
     * @return string
     */
    public function getHash():string 
    {
        return $this->hash;
    }

    /**
     * @param $data
     * @return string
     */
    private function create($data):string
    {
        $result = '';
        foreach ($data as $key => $value) {
            if ($key == 'params' && empty($value) || $key == $this->metaStorageKey) {
                continue;
            } elseif ($key == 'params' && !empty($value)) {
                foreach ($value as $keyParam => $param) {
                    $result .= $keyParam . $param;
                }
            } else {
                $result .= $key . $value;
            }
        }
        $result .= config('integrations.betGames.secret');

        return md5($result);
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
    public function set(string $code)
    {
        $this->cache->add(self::CACHE_KEY . $code, true, self::EXPIRATION_TIME);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isWrong(string $value):bool
    {
        return $value != $this->hash;
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
     * @return int
     */
    private function getTime($value):int 
    {
        list(, $time) = explode('-', $value);
        return $time;
    }
}