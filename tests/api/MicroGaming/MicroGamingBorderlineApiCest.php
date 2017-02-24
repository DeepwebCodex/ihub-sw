<?php
namespace api\MicroGaming;

use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Models\MicroGamingObjectIdMap;
use App\Models\Transactions;
use Carbon\Carbon;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\GameSessionsMock;

class MicroGamingBorderlineApiCest
{
    private $gameID;
    private $options;

    public function _before(\ApiTester $I)
    {
        $this->options = config('integrations.microgaming');
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function _after()
    {
    }

    public function testNoBetWin(\ApiTester $I)
    {
        $this->gameID = random_int(9900000, 99000000);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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
                    'gameid' => $this->gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
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
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errordescription=\'Invalid operation order\']');

        $I->expect('Can see record of transaction applied');
        $I->cantSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testStoragePending(\ApiTester $I)
    {
        $this->gameID = random_int(9900000, 99000000);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'object_id' => MicroGamingObjectIdMap::getObjectId(
                env('TEST_USER_ID'),
                $testUser->getCurrency(),
                $this->gameID
            ),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@errorcode=\'6000\']');
        $I->cantSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testDuplicateTransaction(\ApiTester $I)
    {
        $this->gameID = random_int(9900000, 99000000);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'object_id' => MicroGamingObjectIdMap::getObjectId(
                env('TEST_USER_ID'),
                $testUser->getCurrency(),
                $this->gameID
            ),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''.$testUser->getBalanceInCents().'\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
    }
    
     public function testAccountDuplicateTransaction(\ApiTester $I)
    {
        $this->gameID = random_int(9900000, 99000000);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
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
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        //$I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''.$testUser->getBalanceInCents().'\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $operationId = $I->grabTextContentFromXmlElement('//pkt/methodresponse/result/@exttransactionid');
        $deletedRows = Transactions::where('operation_id', $operationId)->delete();
        $I->assertNotEmpty($deletedRows);
        
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $operationId2 = $I->grabTextContentFromXmlElement('//pkt/methodresponse/result/@exttransactionid');
        $I->assertEquals($operationId, $operationId2);
    }

    public function testZeroWin(\ApiTester $I)
    {
        $this->gameID = random_int(9900000, 99000000);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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
                    'gameid' => $this->gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 0,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'object_id' => MicroGamingObjectIdMap::getObjectId(
                env('TEST_USER_ID'),
                $testUser->getCurrency(),
                $this->gameID
            ),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''.$testUser->getBalanceInCents().'\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testMultiWin(\ApiTester $I)
    {
        require_once "MicroGamingApiCest.php";

        $baseFunc = new MicroGamingApiCest();

        $baseFunc->testMethodPlayOut($I);
        $baseFunc->testMethodPlayOut($I);
    }
}