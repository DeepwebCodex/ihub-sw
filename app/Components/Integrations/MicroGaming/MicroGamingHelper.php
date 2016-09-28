<?php

namespace App\Components\Integrations\MicroGaming;

use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;

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

    /**
     * @param string $remoteSessionId
     * @param string $currency
     * @return string
     */
    public static function generateToken(string $remoteSessionId, string $currency)
    {
        $time = microtime(true);
        return $remoteSessionId . "-" . $time . "-" . self::partToken($remoteSessionId, $time, $currency);
    }

    /**
     * @param string $remoteSessionId
     * @param float $time
     * @param string $currency
     * @return string
     */
    public static function partToken($remoteSessionId, $time, $currency) {
        $currency = self::mapCurrencyCode($currency);
        return md5(sha1($remoteSessionId . config('integrations.microGaming.security_word') . $time . $currency));
    }

    /**
     * @param string $currency
     * @return mixed
     */
    public static function mapCurrencyCode(string $currency)
    {
        $currencyMap = config('integrations.microGaming.list_currency');

        return array_get($currencyMap, $currency, $currency);
    }

    /**
     * @param string $token
     * @return array
     */
    public static function parseToken(string $token){
        $data = explode('-', $token);

        if(empty($data) || !is_array($data) || count($data) != 3)
        {
            throw new ApiHttpException(400, null, CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        return $data;
    }

    public static function confirmTokenHash(string $token, string $remoteSession, string $currency)
    {
        list($remoteSessionId, $time, $hash) = self::parseToken($token);

        if($hash != self::partToken($remoteSession, $time, $currency))
        {
            throw new ApiHttpException(409, "Player token is invalid.", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }
    }

    public static function getTransactionType(string $playType)
    {
        $map = [
            'bet'  => TransactionRequest::TRANS_BET,
            'win'    => TransactionRequest::TRANS_WIN,
            'refund' => TransactionRequest::TRANS_REFUND
        ];

        return array_get($map, $playType);
    }

    public static function getTransactionDirection(string $playType)
    {
        $map = [
            'bet'  => TransactionRequest::D_DEPOSIT,
            'win'    => TransactionRequest::D_WITHDRAWAL,
            'refund' => TransactionRequest::D_WITHDRAWAL
        ];

        return array_get($map, $playType);
    }
}