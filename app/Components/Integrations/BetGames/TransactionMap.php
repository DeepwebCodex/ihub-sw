<?php

namespace App\Components\Integrations\BetGames;

use App\Components\Transactions\TransactionRequest;

/**
 * Class TransactionMap
 * @package App\Components\Integrations\BetGames
 */
class TransactionMap
{
    /**
     * @var string
     */
    private $method;

    /**
     * TransactionMap constructor.
     * @param string $method
     */
    public function __construct(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getType():string 
    {
        $map = [
            'transaction_bet_payin' => TransactionRequest::TRANS_BET,
            'transaction_bet_payout' => TransactionRequest::TRANS_WIN,
        ];

        return $map[$this->method] ?? '';
    }
}