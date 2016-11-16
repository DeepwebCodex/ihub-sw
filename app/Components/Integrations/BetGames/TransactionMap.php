<?php

namespace App\Components\Integrations\BetGames;

use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;

class TransactionMap
{
    private $reason;
    private $deposit;

    public function __construct(string $reason, bool $deposit = false)
    {
        $this->reason = $reason;
        $this->deposit = $deposit;
    }

    public function getType()
    {
        $map = [
            'transaction_bet_payin' => TransactionRequest::TRANS_BET,
            'transaction_bet_payout' => TransactionRequest::TRANS_WIN,
//            'ROUND_CANCEL' => TransactionRequest::TRANS_REFUND,
//            'JACKPOT_END'  => TransactionRequest::TRANS_BONUS_WIN
        ];

        return array_get($map, $this->reason, $this->deposit ? TransactionRequest::TRANS_WIN : TransactionRequest::TRANS_BET);
    }
}