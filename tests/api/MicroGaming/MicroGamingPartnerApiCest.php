<?php
namespace api\MicroGaming;

use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Carbon\Carbon;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use Testing\GameSessionsMock;
use MicroGaming\Helper;

class MicroGamingPartnerApiCest
{
    const URI = '/mg';

    /** @var Params  */
    private $params;

    /** @var Helper */
    private $helper;

    public function __construct()
    {
        $this->params = new Params('microgaming');
        $this->helper = new Helper($this->params);
    }

    public function _before(\ApiTester $I)
    {
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    /** @skip */
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

    /** @skip */
    public function testMultipleTokens(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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

    /** @skip */
    public function testPlayRefund(\ApiTester $I)
    {
        $gameId = random_int(9900000, 99000000);

        $bet = 300;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = $this->helper->getPreparedObjectId($gameId);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->win($objectId, $bet/100, $balance - $bet/100 + $bet/100)
            ->mock($I);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getRequestData($gameId, $bet, 'bet');

        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents - $bet);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $request2 = $this->getRefundRequestData($gameId, $bet);
        $this->sendPlayRequestAndCheckBalance($I, $request2, $balanceInCents);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request2['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    /**
     * @param $gameId
     * @param $amount
     * @param $playType
     *
     * @return array
     */
    protected function getRequestData($gameId, $amount, $playType): array
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
                    'playtype' => $playType,
                    'gameid' => $gameId,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $amount,
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
    protected function getRefundRequestData($gameId, $amount): array
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
                    'amount' => $amount,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];
    }

    public function testPlayRefundForNonExistentBet(\ApiTester $I)
    {
        $gameId = random_int(9900000, 99000000);

        $bet = 300;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = $this->helper->getPreparedObjectId($gameId);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->win($objectId, $bet/100, $balance - $bet/100 + $bet/100)
            ->mock($I);

        $gameId = random_int(9900000, 99000000);
        $request = $this->getRefundRequestData($gameId, $bet);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents);
    }

    /** @skip */
    public function testIdempotencyBet(\ApiTester $I)
    {
        $gameId = random_int(9900000, 99000000);
        $bet = 300;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = $this->helper->getPreparedObjectId($gameId);

        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet/100)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->mock($I);

        $request = $this->getRequestData($gameId, $bet, 'bet');

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents - $bet);
        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents - $bet);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
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

    /** @skip */
    public function testIdempotencyWin(\ApiTester $I)
    {
        $gameId = random_int(9900000, 99000000);
        $bet = 300;
        $win = 600;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = $this->helper->getPreparedObjectId($gameId);

        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet/100 + $win/100)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->win($objectId, $win/100, $balance - $bet/100 + $win/100)
            ->mock($I);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $requestBet = $this->getRequestData($gameId, $bet, 'bet');
        $this->sendPlayRequestAndCheckBalance($I, $requestBet, $balanceInCents - $bet);

        $requestWin = $this->getRequestData($gameId, $win, 'win');

        $this->sendPlayRequestAndCheckBalance($I, $requestWin, $balanceInCents - $bet + $win);
        $this->sendPlayRequestAndCheckBalance($I, $requestWin, $balanceInCents - $bet + $win);
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $requestWin['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    /** @skip */
    public function testIdempotencyRefund(\ApiTester $I)
    {
        $gameId = random_int(9900000, 99000000);
        $bet = 300;
        $win = 600;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = $this->helper->getPreparedObjectId($gameId);

        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet/100 + $win/100)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->win($objectId, $win/100, $balance - $bet/100 + $win/100)
            ->mock($I);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getRequestData($gameId, $bet, 'bet');
        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents - $bet);

        $request = $this->getRequestData($gameId, $win, 'refund');
        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents - $bet + $win);
        $this->sendPlayRequestAndCheckBalance($I, $request, $balanceInCents - $bet + $win);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}
