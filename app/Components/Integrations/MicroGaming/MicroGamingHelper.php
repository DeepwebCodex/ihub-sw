<?php

namespace App\Components\Integrations\MicroGaming;

use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

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
     * @param string $currency
     * @return mixed
     */
    public static function mapCurrencyCode(string $currency)
    {
        $currencyMap = config('integrations.microgaming.list_currency');

        return array_get($currencyMap, $currency, $currency);
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
            'bet'  => TransactionRequest::D_WITHDRAWAL,
            'win'    => TransactionRequest::D_DEPOSIT,
            'refund' => TransactionRequest::D_DEPOSIT
        ];

        return array_get($map, $playType);
    }
}