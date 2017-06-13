<?php
namespace api\MicroGaming;

use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use App\Models\MicroGamingObjectIdMap;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Carbon\Carbon;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use Testing\GameSessionsMock;
use MicroGaming\Helper;

class MicroGamingBorderlineApiCest
{
    private $options;

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
        $this->options = config('integrations.microgaming');

        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function testNoBetWin(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $gameID = random_int(9900000, 99000000);

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
                    'gameid' => $gameID,
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
        $I->cantSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testStoragePending(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 100;
        $gameID = random_int(9900000, 99000000);
        $objectId = $this->helper->getPreparedObjectId($gameID);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->mock($I);

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
                    'gameid' => $gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $bet,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => $this->params->userId,
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => $bet/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => $this->params->partnerId,
            'cashdesk' => $this->params->cashdeskId,
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $this->params->currency,
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'object_id' => MicroGamingObjectIdMap::getObjectId(
                $this->params->userId,
                $this->params->currency,
                $gameID
            ),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(Transactions::class, [
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
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testDuplicateTransaction(\ApiTester $I)
    {
        $gameID = random_int(9900000, 99000000);
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->mock($I);

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
                    'gameid' => $gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 10,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => $this->params->userId,
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => $this->params->partnerId,
            'cashdesk' => $this->params->cashdeskId,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $this->params->currency,
            'foreign_id' => array_get($request, 'methodcall.call.actionid'),
            'object_id' => MicroGamingObjectIdMap::getObjectId(
                $this->params->userId,
                $this->params->currency,
                $gameID
            ),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(Transactions::class, [
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
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''.$balanceInCents.'\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
    }

    /**
     * @skip
     */
     public function testAccountDuplicateTransaction(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $bet = 100;
        $gameID = random_int(9900000, 99000000);
        $objectId = $this->helper->getPreparedObjectId($gameID);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->mock($I);

        $balanceAfter = $balanceInCents - $bet;

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
                    'gameid' => $gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $bet,
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

        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''. $balanceAfter .'\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $operationId = $I->grabTextContentFromXmlElement('//pkt/methodresponse/result/@exttransactionid');
        $deletedRows = Transactions::where('operation_id', $operationId)->delete();
        $I->assertNotEmpty($deletedRows);

        //Duplicate bet
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''. $balanceAfter .'\']');
        $operationId2 = $I->grabTextContentFromXmlElement('//pkt/methodresponse/result/@exttransactionid');
        $I->assertEquals($operationId, $operationId2);
    }

    public function testZeroWin(\ApiTester $I)
    {
        $I->disableMiddleware();

        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $bet = 100;
        $gameID = random_int(9900000, 99000000);
        $objectId = $this->helper->getPreparedObjectId($gameID);

        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet/100)
            ->getFreeOperationId(123)
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->mock($I);

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
                    'gameid' => $gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => 0,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        // BET
        $requestBet = $request;

        $requestBet['methodcall']['call']['playtype'] = 'bet';
        $requestBet['methodcall']['call']['gameid'] = $gameID;
        $requestBet['methodcall']['call']['amount'] = $bet;
        $I->sendPOST('/mg', $requestBet);

        // WIN
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse[@name=\'play\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@seq=\'24971455-aecc-4a69-8494-f544d49db3da\']');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''.($balanceInCents - $bet).'\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testMultiWin(\ApiTester $I)
    {
        $gameID = random_int(9900000, 99000000);

        $bet = 300;
        $win = 400;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = $this->helper->getPreparedObjectId($gameID);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet/100, $balance - $bet/100)
            ->win($objectId, $win/100, $balance - $bet/100 + $win/100)
            ->mock($I);

        $this->playIn($I, $gameID, $bet);

        // win1
        $this->playOut($I, $gameID, $win, $balanceInCents - $bet + $win);

        // win2
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->win($objectId, $win/100, $balance - $bet/100 + 2*$win/100)
            ->mock($I);

        $this->playOut($I, $gameID, $win, $balanceInCents - $bet + 2*$win);
    }


    private function playOut(\ApiTester $I, $gameID, $amount, $balance)
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
                    'gameid' => $gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $amount,
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
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@token');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result/@exttransactionid');
        $I->canSeeXmlResponseMatchesXpath('//pkt/methodresponse/result[@balance=\''.$balance.'\']');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['methodcall']['call']['actionid'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    private function playIn(\ApiTester $I, $gameID, $bet)
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
                    'gameid' => $gameID,
                    'actionid' => random_int(9900000, 99000000),
                    'amount' => $bet,
                    'gamereference' => str_random(),
                    'token' => md5(uniqid('microgaming'.random_int(-99999,999999)))
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->haveHttpHeader("X_FORWARDED_PROTO", "ssl");
        $I->sendPOST('/mg', $request);
    }
}