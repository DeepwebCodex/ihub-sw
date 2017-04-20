<?php

namespace App\Components\Integrations\DriveMedia\DriveCasino;

use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

class DriveCasinoHelper
{
    private static $map = [
        'getBalance' => 'balance',
        'writeBet' => 'bet',
    ];

    public static function mapMethod(string $methodName)
    {
        return array_get(self::$map, $methodName, $methodName);
    }

    public static function getTransactions(float $bet, float $win_lose, $transactions = []):array
    {
        if ($bet != 0) {
            array_push($transactions, [
                'amount' => $bet,
                'type' => TransactionRequest::TRANS_BET
            ]);
        } else {
            array_push($transactions, [
                'amount' => $win_lose,
                'type' => TransactionRequest::TRANS_WIN
            ]);
        }

        return $transactions;
    }
}