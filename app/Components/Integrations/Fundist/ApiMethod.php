<?php

namespace App\Components\Integrations\Fundist;

/**
 * Class ApiMethod
 * @package App\Components\Integrations\Fundist
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

    public function isOffline()
    {
        return in_array($this->method, ['ping', 'roundinfo']);
    }

    /**
     * @return string
     */
    public function isTransaction(): string
    {
        return in_array($this->method, ['debit', 'credit']);
    }
}