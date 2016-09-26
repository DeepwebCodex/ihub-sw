<?php

namespace App\Components\Transactions\Decorators;


use App\Components\Transactions\TransactionResponse;

class BalanceTransactionCents
{
    private $response;

    public function __construct(TransactionResponse $response)
    {
        $this->response = $response;
    }

    public function getBalanceInCents()
    {
        $balance = $this->response->getBalance();

        if($balance !== null){
            return $balance * 100;
        }

        return null;
    }
}