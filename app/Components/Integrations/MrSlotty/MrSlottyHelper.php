<?php

namespace App\Components\Integrations\MrSlotty;
use App\Components\Transactions\TransactionRequest;

/**
 * Class MrSlottyHelper
 * @package App\Components\Integrations\MrSlotty
 */
class MrSlottyHelper
{
    private static $map = [
        'balance'   => 'balance',
        'bet'       => 'bet',
        'win'       => 'win',
        'bet_win'   => 'betWin'
    ];

    /**
     * @param string $methodName
     * @return mixed
     */
    public static function mapMethod(string $methodName)
    {
        return array_get(self::$map, $methodName, $methodName);
    }

    /**
     * @param int $amount
     * @return float|int
     */
    public static function amountCentsToWhole(int $amount)
    {
        return $amount / 100;
    }

    /**
     * @param int $bet
     * @param int $win
     * @param int $bet_transaction
     * @param int $win_transaction
     * @param array $transactions
     * @return array
     */
    public static function getTransactions(int $bet, int $win, int $bet_transaction, int $win_transaction, array $transactions = []):array
    {
        array_push($transactions, [
            'amount' => $bet,
            'transaction_id' => $bet_transaction,
            'type' => TransactionRequest::TRANS_BET,
            'direction' => TransactionRequest::D_WITHDRAWAL
        ]);

        if($win > 0) {
            array_push($transactions, [
                'amount' => $win,
                'transaction_id' => $win_transaction,
                'type' => TransactionRequest::TRANS_WIN,
                'direction' => TransactionRequest::D_DEPOSIT
            ]);
        }

        return $transactions;
    }
}