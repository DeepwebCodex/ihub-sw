<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 12:54 PM
 */

namespace App\Components\Users\Traits;


use Illuminate\Support\Facades\Redis;

trait SessionCurrency
{
    protected $redisKey;

    protected function setSessionCurrency($currency, $userId){
        Redis::setEx($this->redisKey . $userId . ':currency', 605, $currency);
    }

    protected function validateSessionCurrency($activeCurrency, $userId){
        $sessionCurrency = Redis::get($this->redisKey . $userId. ':currency');
        return $sessionCurrency == $activeCurrency;
    }
}