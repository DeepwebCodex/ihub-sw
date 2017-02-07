<?php

namespace App\Components\Integrations\NetEnt;

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
            'balance' => 'getBalance',
            'roundinfo' => 'roundInfo',
            'debit' => 'bet',
            'credit' => 'win',
        ];

        return $map[$this->method] ?? '';
    }

    /**
     * @return string
     */
    public function isTransaction(): string
    {
        return in_array($this->method, ['debit', 'credit']);
    }
}