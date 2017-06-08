<?php
namespace api\MicroGaming;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Carbon\Carbon;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Testing\GameSessionsMock;
use Testing\MicroGaming\AccountManagerMock;
use Testing\MicroGaming\Params;

class MicroGamingPartnerFailureApiCest
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

    public function testLoginTokenFailure(\ApiTester $I)
    {
        $token = md5(uniqid('microgaming' . random_int(-99999, 999999)));
        $request = [
            'methodcall' => [
                'name' => 'login',
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

        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'login\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6002\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The player token expired.\']');
    }

    public function testGetBalanceFailure(\ApiTester $I)
    {
        $request = [
            'methodcall' => [
                'name' => 'getbalance',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'poker',
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
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'getbalance\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The selected methodcall.system is invalid.\']');
    }

    public function testRefreshTokenFailure(\ApiTester$I)
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
                    'token' => ''
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
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The methodcall.call.token field is required.\']');
    }

    public function testPlayBetFailure(\ApiTester$I)
    {
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
                    'gameid' => random_int(9900000, 99000000),
                    'actionid' => '',
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming' . random_int(-99999, 999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The methodcall.call.actionid field is required.\']');
    }

    public function testPlayWinFailure(\ApiTester$I)
    {
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
                    'gameid' => random_int(9900000, 99000000),
                    'actionid' => '',
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => ''
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The methodcall.call.token field is required.\']');
    }

    public function testEndGameFailure(\ApiTester$I)
    {
        $request = [
            'methodcall' => [
                'name' => 'endgame',
                'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                'system' => 'casino',
                'auth' => [
                    'login' => 'microgaming',
                    'password' => 'hawai'
                ],
                'call' => [
                    'seq' => '24971455-aecc-4a69-8494-f544d49db3da',
                    'token' => ''
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'endgame\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The methodcall.call.token field is required.\']');
    }

    public function testRefundFailure(\ApiTester $I)
    {
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
                    'playtype' => 'refund',
                    'gameid' => random_int(9900000, 99000000),
                    'actionid' => '',
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => ''
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST(self::URI, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'The methodcall.call.token field is required.\']');
    }
}
