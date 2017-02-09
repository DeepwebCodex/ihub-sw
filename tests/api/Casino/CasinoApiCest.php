<?php

use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\GameSessionsMock;


class CasinoApiCest
{

    private $objectId;
    private $user_balance;

    public function _before(\ApiTester $I)
    {
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function _after()
    {
    }

    // tests
    public function testMethodNotFound(ApiTester $I)
    {
        $I->sendGET('/casino');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->expect('both items are in response');
        $I->seeResponseContainsJson(['status' => false, 'message' => 'Unknown imprint', 'token' => '']);
    }

    public function testMethodAuth(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/casino/auth', [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'signature'  => CasinoHelper::generateActionSignature(['api_id' => 15, 'time' => time(), 'token'  => 'HSKSOOJH9762tSDSDF']),
            'time'   => time()
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success', 'user_id' => env('TEST_USER_ID')]);
    }

    public function testMethodGetBalance(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/casino/getbalance', [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'signature'  => CasinoHelper::generateActionSignature(['api_id' => 15, 'time' => time(), 'token'  => 'HSKSOOJH9762tSDSDF']),
            'time'   => time()
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('contains user balance');
        $I->canSeeResponseContains("\"balance\"");
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);
    }

    public function testMethodRefreshToken(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/casino/refreshtoken', [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'signature'  => CasinoHelper::generateActionSignature(['api_id' => 15, 'time' => time(), 'token'  => 'HSKSOOJH9762tSDSDF']),
            'time'   => time()
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);
    }

    public function testMethodPayIn(ApiTester $I)
    {
        $this->objectId = random_int(100000, 9900000);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $this->objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => 10,
            'time'   => time()
        ];

        $I->disableMiddleware();
        $I->sendPOST('/casino/payin', array_merge($request, [
            'signature'  => CasinoHelper::generateActionSignature($request),
        ]));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->canSeeResponseContains("\"balance\"");
        $this->user_balance = $I->grabDataFromResponseByJsonPath('balance');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['transaction_id'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testMethodPayOut(ApiTester $I)
    {
        $this->testMethodPayIn($I);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $this->objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => 10,
            'user_id' => env('TEST_USER_ID'),
            'time'   => time(),
            'type_operation' => 'rollback'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/casino/payout', array_merge($request, [
            'signature'  => CasinoHelper::generateActionSignature($request),
        ]));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->canSeeResponseContains("\"balance\"");
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);

        $expected = $this->user_balance[0] + $request['amount'];

        $I->assertEquals([$expected], $I->grabDataFromResponseByJsonPath('balance'), "Balance does not match");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['transaction_id'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testGenToken(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/casino/gen_token');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => '']);
    }
}