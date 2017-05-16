<?php

namespace WirexGaming;

use Testing\Params;

class TestData
{
    private $options;

    public function __construct()
    {
        $this->options = config('integrations.wirexGaming');
    }

    /**
     * @return bool|string
     */
    public function makeTransactionUid()
    {
        $time = microtime(true) * 10;
        return substr($time, 1, 10);
    }

    protected function getUserUid()
    {
        return $this->makeUid(env('TEST_USER_ID'));
    }

    /**
     * @param $oid
     * @return int
     */
    protected function makeUid($oid)
    {
        $previousContextId = config('integrations.wirexGaming.previous_context_id');
        return ($oid << 16) + $previousContextId;
    }

    public function getPersistentSession()
    {
        $sessionId = 123;
        $sessionMagic = 'qwerty';

        return [
            'S:Body' => [
                'ns2:getPersistentSession' => [
                    'request' => [
                        'clientPid' => array_get($this->options, 'client_pid'),
                        'serverPid' => array_get($this->options, 'server_pid'),
                        'partyOriginatingUid' => $this->getUserUid(),
                        'remotePersistentSessionId' => $sessionId,
                        'remotePersistentSessionMagic' => $sessionMagic,
                    ]
                ]
            ],
        ];
    }

    public function getUserData()
    {
        return [
            'S:Body' => [
                'ns2:getUserData' => [
                    'request' => [
                        'clientPid' => array_get($this->options, 'client_pid'),
                        'serverPid' => array_get($this->options, 'server_pid'),
                        'partyOriginatingUid' => $this->getUserUid()
                    ]
                ]
            ],
        ];
    }

    public function getAvailableBalance()
    {
        return [
            'S:Body' => [
                'ns2:getAvailableBalance' => [
                    'request' => [
                        'clientPid' => array_get($this->options, 'client_pid'),
                        'serverPid' => array_get($this->options, 'server_pid'),
                        'partyOriginatingUId' => $this->getUserUid()
                    ],
                ]
            ],
        ];
    }

    public function addWithdrawEntry()
    {
        $transactionUid = $this->makeTransactionUid();

        return [
            'S:Body' => [
                'ns2:addWithdrawEntry' => [
                    'accountEntryPlatformRequest' => [
                        'clientPid' => array_get($this->options, 'client_pid'),
                        'serverPid' => array_get($this->options, 'server_pid'),
                        'callerContextId' => 0,
                        'contextId' => 0,
                        'sourceContextId' => 0,
                        'partyOriginatingUid' => $this->getUserUid(),
                        'transactionUid' => Params::OBJECT_ID, // $transactionUid,
                        'sessionToken' => '123',
                        'accountEntryDetailed' => [
                            'accountEntry' => [
                                'amount' => Params::AMOUNT,
                                'currency' => Params::CURRENCY,
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }

    public function rollbackWithdraw($betTransactionUid)
    {
        $transactionUid = $this->makeTransactionUid();

        return [
            'S:Body' => [
                'ns2:rollBackWithdraw' => [
                    'transactionRequest' => [
                        'clientPid' => array_get($this->options, 'client_pid'),
                        'serverPid' => array_get($this->options, 'server_pid'),
                        'callerContextId' => 0,
                        'contextId' => 0,
                        'originatingPid' => 0,
                        'partyOriginatingUid' => $this->getUserUid(),
                        'transactionUid' => Params::OBJECT_ID, // $transactionUid,
                        'relatedTransUid' => Params::OBJECT_ID, // $betTransactionUid,
                        'sessionToken' => '123',
                        'amount' => Params::AMOUNT,
                    ],
                ]
            ],
        ];
    }

    public function addDepositEntry($betTransactionUid)
    {
        $transactionUid = $this->makeTransactionUid();

        return [
            'S:Body' => [
                'ns2:addDepositEntry' => [
                    'accountEntryPlatformRequest' => [
                        'clientPid' => array_get($this->options, 'client_pid'),
                        'serverPid' => array_get($this->options, 'server_pid'),
                        'callerContextId' => 0,
                        'contextId' => 0,
                        'sourceContextId' => 0,
                        'originatingPid' => 0,
                        'partyOriginatingUid' => $this->getUserUid(),
                        'transactionUid' => Params::OBJECT_ID, // $transactionUid,
                        'relatedTransUid' => Params::OBJECT_ID, // $betTransactionUid,
                        'sessionToken' => '123',
                        'accountEntryDetailed' => [
                            'accountEntry' => [
                                'amount' => Params::WIN_AMOUNT,
                                'currency' => Params::CURRENCY,
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }
}
