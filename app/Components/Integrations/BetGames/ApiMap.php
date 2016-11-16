<?php

namespace App\Components\Integrations\BetGames;


class ApiMap
{
    public function get($method)
    {
        $map = [
            'ping' => 'ping',
            'get_account_details' => 'account',
            'refresh_token' => 'refreshToken',
            'request_new_token' => 'requestNewToken',
            'get_balance' => 'getBalance',
            'transaction_bet_payin' => 'bet',
            'transaction_bet_payout' => 'win',
        ];
        
        return (empty($map[$method])) ? 'error' : $map[$method];
    }
}