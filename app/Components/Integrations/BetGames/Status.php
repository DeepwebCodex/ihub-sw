<?php

namespace App\Components\Integrations\BetGames;

class Status
{

    private $key;
    private $data = [
        'wrong_signature' => [
            'code' => 1,
            'message' => 'wrong signature'
        ],
        'time_expired' => [
            'code' => 2,
            'message' => 'request is expired',
        ],
        'wrong_token' => [
            'code' => 3,
            'message' => 'invalid token',
        ]
    ];

    public function __construct($key = '')
    {
        $this->key = $key;
    }

    public function getCode()
    {
        return $this->data[$this->key]['code'];
    }

    public function getMessage()
    {
        return $this->data[$this->key]['message'];
    }
}