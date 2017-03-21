<?php

namespace App\Components\Integrations\DriveMedia\Playtech;

use App\Components\Transactions\TransactionRequest;

class PlaytechHelper
{
    private static $map = [
        'getBalance' => 'balance',
        'writeBet' => 'bet',
    ];

    public static function mapMethod(string $methodName)
    {
        return array_get(self::$map, $methodName, $methodName);
    }

    public static function getTransactions(float $bet, float $win_lose, string $bet_info, $transactions = []):array
    {
        if ($bet != 0) {
            if ($win_lose >= 0) {
                array_push($transactions, [
                    'amount' => $bet,
                    'type' => TransactionRequest::TRANS_BET
                ]);

                array_push($transactions, [
                    'amount' => $win_lose,
                    'type' => TransactionRequest::TRANS_WIN
                ]);
            } else {
                if ($bet == ($win_lose * -1)) {
                    array_push($transactions, [
                        'amount' => $bet,
                        'type' => TransactionRequest::TRANS_BET
                    ]);
                } else {
                    array_push($transactions, [
                        'amount' => $bet,
                        'type' => TransactionRequest::TRANS_BET
                    ]);
                    array_push($transactions, [
                        'amount' => $win_lose + $bet,
                        'type' => TransactionRequest::TRANS_WIN
                    ]);
                }
            }
        } else {
            $type = TransactionRequest::TRANS_WIN;
            if($bet_info == 'bonus')
            {
                $type = TransactionRequest::TRANS_BONUS_WIN;
            }
            array_push($transactions, [
                'amount' => $win_lose,
                'type' => $type
            ]);
        }

        return $transactions;
    }
}