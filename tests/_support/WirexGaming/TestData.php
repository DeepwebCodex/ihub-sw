<?php

namespace WirexGaming;

use Testing\DriveMedia\Params;

class TestData
{
    private $options;

    public function __construct(Params $params)
    {
        $this->params = $params;
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
        return $this->makeUid($this->params->userId);
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

    public function addWithdrawEntry($transactionUid, $amount)
    {
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
                        'transactionUid' => $transactionUid,
                        'sessionToken' => '123',
                        'accountEntryDetailed' => [
                            'accountEntry' => [
                                'amount' => $amount,
                                'currency' => $this->params->currency,
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }

    public function rollbackWithdraw($transactionUid, $betTransactionUid, $amount)
    {
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
                        'transactionUid' => $transactionUid,
                        'relatedTransUid' => $betTransactionUid,
                        'sessionToken' => '123',
                        'amount' => $amount,
                    ],
                ]
            ],
        ];
    }

    public function addDepositEntry($transactionUid, $betTransactionUid, $amount)
    {
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
                        'transactionUid' => $transactionUid,
                        'relatedTransUid' => $betTransactionUid,
                        'sessionToken' => '123',
                        'accountEntryDetailed' => [
                            'accountEntry' => [
                                'amount' => $amount,
                                'currency' => $this->params->currency,
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }
}
