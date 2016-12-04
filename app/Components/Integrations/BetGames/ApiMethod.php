<?php

namespace App\Components\Integrations\BetGames;

/**
 * Class ApiMethod
 * @package App\Components\Integrations\BetGames
 */
class ApiMethod
{

    /**
     * @var string
     */
    private $method;

    /**
     * ApiMethod constructor.
     * @param string $method
     */
    public function __construct(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function get():string 
    {
        $map = [
            'ping' => 'ping',
            'get_account_details' => 'account',
            'refresh_token' => 'refreshToken',
            'request_new_token' => 'newToken',
            'get_balance' => 'getBalance',
            'transaction_bet_payin' => 'bet',
            'transaction_bet_payout' => 'win',
        ];

        return $map[$this->method] ?? '';
    }
}