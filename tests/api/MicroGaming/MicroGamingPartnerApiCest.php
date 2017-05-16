<?php
namespace api\MicroGaming;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Carbon\Carbon;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\GameSessionsMock;
use Testing\MicroGaming\AccountManagerMock;
use Testing\MicroGaming\Params;

class MicroGamingPartnerApiCest
{
    const URI = '/mg';
    public function __construct()
    {
        $this->params = new Params();
    }

    public function _before(\ApiTester $I)
    {
        if($this->params->enableMock) {
            $mock = (new AccountManagerMock())->getMock();
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }

        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

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

    public function testPlayRefund(\ApiTester $I)
    {
        $gameId = $this->params->getObjectId(Params::REFUND_OBJECT_ID);

        $userBalance = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests')->getBalanceInCents();

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getRequestData($gameId, $this->params->getAmount(), 'bet');

        $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance - $this->params->getAmount());

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $request2 = $this->getRefundRequestData($gameId, $this->params->getAmount());
        $this->sendPlayRequestAndCheckBalance($I, $request2, $userBalance);

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
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $userBalance = $testUser->getBalanceInCents();

        $gameId = random_int(9900000, 99000000);
        $request = $this->getRefundRequestData($gameId, $this->params->getAmount());

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance);
    }

    public function testIdempotencyBet(\ApiTester $I)
    {
        $gameId = $this->params->getObjectId(Params::IDEMPOTENCY_OBJECT_ID);
        $request = $this->getRequestData($gameId, $this->params->getAmount(), 'bet');

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $userBalance = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests')->getBalanceInCents();

        //$this->sendAndCheckPlayRequestSeveralTimes($I, $request, $userBalance - $this->params->getAmount());

        $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance - $this->params->getAmount());

        //TODO: second time user balance must be the same, but it is no way to mock getBalance() twice in single mock object
        if(!$this->params->enableMock) {
            $this->sendPlayRequestAndCheckBalance($I, $request, $userBalance - $this->params->getAmount());
        }

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

    public function testIdempotencyWin(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $gameId = $this->params->getObjectId(Params::IDEMPOTENCY_OBJECT_ID);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getRequestData($gameId, $this->params->getAmount(), 'bet');
        $this->sendPlayRequestAndCheckBalance($I, $request, $testUser->getBalanceInCents() - $this->params->getAmount());

        $requestWin = $this->getRequestData($gameId, $this->params->getAmount(), 'win');

        $this->sendAndCheckPlayRequestSeveralTimes($I, $requestWin, $testUser->getBalanceInCents());

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $requestWin['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testIdempotencyRefund(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $gameId = $this->params->getObjectId(Params::IDEMPOTENCY_OBJECT_ID);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");

        $request = $this->getRequestData($gameId, $this->params->getAmount(), 'bet');
        $this->sendPlayRequestAndCheckBalance($I, $request, $testUser->getBalanceInCents() - $this->params->getAmount());

        $request = $this->getRequestData($gameId, $this->params->getAmount(), 'refund');
        $this->sendAndCheckPlayRequestSeveralTimes($I, $request, $testUser->getBalanceInCents());

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}
