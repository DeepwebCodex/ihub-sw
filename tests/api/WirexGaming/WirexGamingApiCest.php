<?php

namespace api\WirexGaming;

use App\Components\Integrations\GameSession\GameSessionService;
use iHubGrid\Accounting\ExternalServices\AccountManager;
use iHubGrid\Accounting\Users\IntegrationUser;
use Testing\GameSessionsMock;
use Testing\MicroGaming\AccountManagerMock;
use Testing\MicroGaming\Params;

/**
 * Class WirexGamingApiCest
 * @package api\WirexGaming
 */
class WirexGamingApiCest
{
    private $gameID;

    public function __construct()
    {
        $this->params = new Params();
    }

    public function _before(\ApiTester $I)
    {
        $I->mockAccountManager($I, config('integrations.wirexGaming.service_id'));
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    /*public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendGET('/wirex');
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('both items are in response');
        $I->seeXmlResponseIncludes('<message>Unknown method</message>');
    }

    public function testGetPersistentSession(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $sessionId = 123;
        $sessionMagic = 'qwerty';

        $request = [
            'S:Body' => [
                'ns2:getPersistentSession' => [
                    'request' => [
                        'partyOriginatingUid' => 123,
                        'remotePersistentSessionId' => $sessionId,
                        'remotePersistentSessionMagic' => $sessionMagic,
                    ]
                ]
            ],
        ];
        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');
    }

    public function testGetUserData(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'S:Body' => [
                'ns2:getUserData' => [
                    'request' => [
                        'clientPid' => 65487,
                        'serverPid' => 50088,
                        'partyOriginatingUid' => 12436392
                    ]
                ]
            ],
        ];

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');
    }

    public function testGetAvailableBalance(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'S:Body' => [
                'ns2:getAvailableBalance' => [
                    'request' => [
                        'clientPid' => 65487,
                        'serverPid' => 50088,
                        'partyOriginatingUId' => 12436392
                    ],
                ]
            ],
        ];

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');
    }*/

    /*
    public function testAddWithdrawEntry(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
        $this->gameID = $this->params->getObjectId();

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
                    'playtype' => 'bet',
                    'gameid' => $this->gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $this->params->getAmount(),
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\'' . ($testUser->getBalanceInCents() - $this->params->getAmount()) . '\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testRollbackWithdraw(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
        $this->gameID = $this->params->getObjectId();

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
                    'playtype' => 'bet',
                    'gameid' => $this->gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $this->params->getAmount(),
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\'' . ($testUser->getBalanceInCents() - $this->params->getAmount()) . '\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testAddDepositEntry(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
        $this->gameID = $this->params->getObjectId();

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
                    'playtype' => 'bet',
                    'gameid' => $this->gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $this->params->getAmount(),
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\'' . ($testUser->getBalanceInCents() - $this->params->getAmount()) . '\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testCancelTransaction(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
        $this->gameID = $this->params->getObjectId();

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
                    'playtype' => 'bet',
                    'gameid' => $this->gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $this->params->getAmount(),
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\'' . ($testUser->getBalanceInCents() - $this->params->getAmount()) . '\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }*/
}
