<?php

namespace App\Components\Integrations\BetGames;

class TokenValidation
{
    private $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function isExpired():bool
    {
        return (time() - $this->token->getTime()) > $this->token->getExpirationTime();
    }

    public function isWrongCurrency(string $currency):bool
    {
        return $currency == $this->token->getCurrency();
    }
}