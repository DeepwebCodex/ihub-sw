<?php

namespace App\Components\Integrations\MicroGaming;

class MicroGamingHelper
{
    private static $map = [
        'login'         => 'logIn',
        'getbalance'    => 'getBalance',
        'play'          => 'play',
        'endgame'       => 'endGame',
        'refreshtoken'  => 'refreshToken'
    ];

    /**
     * @param string $methodName
     * @return string
     */
    public static function mapMethod(string $methodName)
    {
        return array_get(self::$map, $methodName, $methodName);
    }
}