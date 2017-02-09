<?php

use App\Components\Transactions\TransactionRequest;
use Carbon\Carbon;

class MicroGamingPartnerApiCest
{
    const URI = '/mg';

    public function testRefreshToken(\ApiTester $I)
    {
        $request = [
            'methodcall' => [
                'name' => 'refreshtoken',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'casino',
                'auth' => [
                    'login' => 'microgaming',
                    'password' => 'hawai'
                ],
                'call' => [
                    'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'refreshtoken\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
    }

    public function testInvalidAuthCredentials(\ApiTester $I)
    {
        $request = [
            'methodcall' => [
                'name' => 'login',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'casino',
                'auth' => [
                    'login' => 'ID30BF',
                    'password' => 'RA35U5'
                ],
                'call' => [
                    'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'login\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6003\']');
    }

    public function testMultipleTokens(\ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $count = 3;
        $token = md5(uniqid('microgaming' . random_int(-99999, 999999)));

        while ($count--) {
            $request = [
                'methodcall' => [
                    'name' => 'getbalance',
                    'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                    'system' => 'casino',
                    'auth' => [
                        'login' => 'microgaming',
                        'password' => 'hawai'
                    ],
                    'call' => [
                        'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                        'token' => $token
                    ]
                ]
            ];

            $I->disableMiddleware();
            $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
            $I->sendPOST(self::URI, $request);
            $I->seeResponseCodeIs(200);
            $I->canSeeResponseIsXml();
            $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'getbalance\']');
            $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
            $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\'' . $testUser->getBalanceInCents() . '\']');
            $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        }
    }

    public function testPlayRefund(\ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $gameId = random_int(9900000, 99000000);

        $userBalance = $testUser->getBalanceInCents();

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getBetRequestData($gameId);

        $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance - 10);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $request = $this->getRefundRequestData($gameId);
        $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    /**
     * @param $gameId
     * @return array
     */
    protected function getBetRequestData($gameId): array
    {
        return [
            'methodcall' => [
                'name' => 'play',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'casino',
                'auth' => [
                    'login' => 'microgaming',
                    'password' => 'hawai'
                ],
                'call' => [
                    'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                    'playtype' => 'bet',
                    'gameid' => $gameId,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];
    }

    /**
     * @param $I
     * @param $request
     * @param $balanceForCheck
     */
    protected function sendPlayRequestAndCheckBalance($I, $request, $balanceForCheck)
    {
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\'' . $balanceForCheck . '\']');
    }

    /**
     * @param $gameId
     * @return array
     */
    protected function getRefundRequestData($gameId): array
    {
        return [
            'methodcall' => [
                'name' => 'play',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'casino',
                'auth' => [
                    'login' => 'microgaming',
                    'password' => 'hawai'
                ],
                'call' => [
                    'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                    'playtype' => 'refund',
                    'gameid' => $gameId,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];
    }

    public function testPlayRefundForNonExistentBet(\ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $userBalance = $testUser->getBalanceInCents();

        $gameId = random_int(9900000, 99000000);
        $request = $this->getRefundRequestData($gameId);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance);
    }

    public function testIdempotencyBet(\ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $gameId = random_int(9900000, 99000000);
        $request = $this->getBetRequestData($gameId);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $this->sendAndCheckPlayRequestSeveralTimes($I, $request, $testUser->getBalanceInCents() - 10);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    /**
     * @param $I
     * @param $request
     * @param $balanceForCheck
     */
    protected function sendAndCheckPlayRequestSeveralTimes(\ApiTester $I, $request, $balanceForCheck)
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->sendPlayRequestAndCheckBalance($I, $request, $balanceForCheck);
        }
    }

    public function testIdempotencyWin(\ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $gameId = random_int(9900000, 99000000);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getBetRequestData($gameId);
        $this->sendPlayRequestAndCheckBalance($I, $request, $testUser->getBalanceInCents() - 10);

        $request = [
            'methodcall' => [
                'name' => 'play',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'casino',
                'auth' => [
                    'login' => 'microgaming',
                    'password' => 'hawai'
                ],
                'call' => [
                    'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                    'playtype' => 'win',
                    'gameid' => $gameId,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $this->sendAndCheckPlayRequestSeveralTimes($I, $request, $testUser->getBalanceInCents());

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testIdempotencyRefund(\ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $gameId = random_int(9900000, 99000000);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getBetRequestData($gameId);
        $this->sendPlayRequestAndCheckBalance($I, $request, $testUser->getBalanceInCents() - 10);

        $request = $this->getRefundRequestData($gameId);
        $this->sendAndCheckPlayRequestSeveralTimes($I, $request, $testUser->getBalanceInCents());

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}
