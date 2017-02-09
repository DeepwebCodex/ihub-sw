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
     * @see App\Http\Controllers\Api\BetGamesController::ping,
     * @see App\Http\Controllers\Api\BetGamesController::account,
     * @see App\Http\Controllers\Api\BetGamesController::refreshToken,
     * @see App\Http\Controllers\Api\BetGamesController::newToken,
     * @see App\Http\Controllers\Api\BetGamesController::getBalance,
     * @see App\Http\Controllers\Api\BetGamesController::bet,
     * @see App\Http\Controllers\Api\BetGamesController::win,
     *
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

    public function isOffline()
    {
        return in_array($this->method, ['ping', 'transaction_bet_payout']);
    }
}