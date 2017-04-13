<?php


namespace App\Components\Integrations\GameArt;

use App\Components\Transactions\Strategies\GameArt\ProcessGameArt;
use App\Components\Transactions\TransactionRequest;
use App\Components\Transactions\TransactionHandler;

class GameArtHelper
{

    private static $map = [
        'balance'   => 'balance',
        'credit'    => 'credit',
        'debit'     => 'debit'
    ];

    public static function mapMethod(string $methodName):string
    {
        return array_get(self::$map, $methodName, $methodName);
    }

    public static function handleTransaction($transactionRequest, $user)
    {
        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessGameArt());

        return $transactionResponse;
    }

    public static function getTransactionType(string $playType)
    {
        $map = [
            'WIN'       => TransactionRequest::TRANS_WIN,
            'WIN_FREE'  => TransactionRequest::TRANS_BONUS_WIN,
            'REF'       => TransactionRequest::TRANS_REFUND,
            'BET'       => TransactionRequest::TRANS_BET,
            'BET_FREE'  => TransactionRequest::TRANS_BET,
        ];

        return array_get($map, $playType);
    }

    public static function getTransactionDirection(string $playType)
    {
        $map = [
            'WIN'       => TransactionRequest::D_DEPOSIT,
            'WIN_FREE'  => TransactionRequest::D_DEPOSIT,
            'REF'       => TransactionRequest::D_DEPOSIT,
            'BET'       => TransactionRequest::D_WITHDRAWAL,
            'BET_FREE'  => TransactionRequest::D_WITHDRAWAL
        ];

        return array_get($map, $playType);
    }


}