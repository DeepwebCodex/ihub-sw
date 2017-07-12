<?php

namespace App\Components\Integrations\MrSlotty;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

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
     * @param string $bet_transaction
     * @param string $win_transaction
     * @param array $transactions
     * @return array
     */
    public static function getTransactions(int $bet, int $win, string $bet_transaction, string $win_transaction, array $transactions = []):array
    {
        array_push($transactions, [
            'amount' => $bet,
            'transaction_id' => $bet_transaction,
            'type' => TransactionRequest::TRANS_BET,
            'direction' => TransactionRequest::D_WITHDRAWAL
        ]);
        array_push($transactions, [
            'amount' => $win,
            'transaction_id' => $win_transaction,
            'type' => TransactionRequest::TRANS_WIN,
            'direction' => TransactionRequest::D_DEPOSIT
        ]);

        return $transactions;
    }

    /**
     * @param $roundId
     * @return int
     */
    public static function getObjectId($roundId):int
    {
        return hexdec(substr($roundId, 0, 15));
    }
}