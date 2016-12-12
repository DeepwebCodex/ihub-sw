<?php

namespace App\Components\Integrations\BetGames;

use App\Components\Transactions\TransactionHelper;

class Error
{
    private $key;
    const NO = 'no';
    const SIGNATURE = 'signature';
    const TIME = 'time';
    const TOKEN = 'token';

    private $data = [
        // validation error codes
        self::NO => [
            'code' => 0,
            'message' => '',
        ],
        self::SIGNATURE => [
            'code' => 1,
            'message' => 'wrong signature'
        ],
        self::TIME => [
            'code' => 2,
            'message' => 'request is expired',
        ],
        self::TOKEN => [
            'code' => 3,
            'message' => 'invalid token',
        ],

        // map account manager transaction errors
        TransactionHelper::BAD_OPERATION_ORDER => [
            'code' => 700,
            'message' => 'there is no PAYIN with provided bet_id',
        ],
        TransactionHelper::INSUFFICIENT_FUNDS => [
            'code' => 4,
            'message' => 'insufficient funds',
        ],
        TransactionHelper::DUPLICATE => [
            'code' => 3,
            'message' => 'invalid token',
        ],
        TransactionHelper::ACCOUNT_DENIED => [
            'code' => null,
            'message' => 'access to account denied',
        ],
        TransactionHelper::UNKNOWN => [
            'code' => null,
            'message' => 'unknown error',
        ],
    ];

    /**
     * Error constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $params = array_fill_keys([
            'method', 'params', 'params.amount', 'params.currency', 'params.bet_id', 'params.transaction_id',
            'params.retrying', 'params.player_id'], [
                'code' => 1,
                'message' => 'wrong signature',
            ]);
        $this->data = array_merge($this->data, $params);
        $this->key = isset($this->data[$key]) ? $key : TransactionHelper::UNKNOWN;
    }

    public function getCode()
    {
        return $this->data[$this->key]['code'];
    }

    public function getMessage():string 
    {
        return $this->data[$this->key]['message'];
    }

    public function isValidationCode():bool 
    {
        return in_array($this->key, [
            self::NO, self::SIGNATURE, self::TIME, self::TOKEN,
            'params', 'params.method', 'params.amount', 'params.currency', 'params.bet_id', 
            'params.transaction_id', 'params.retrying', 'params.player_id'
        ]);
    }
}