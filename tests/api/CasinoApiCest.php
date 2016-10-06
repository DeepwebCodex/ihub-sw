<?php

use App\Components\ExternalServices\RemoteSession;

class CasinoApiCest
{

    protected function _before()
    {
    }

    protected function _after()
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
            'signature'  => \App\Components\Integrations\Casino\CasinoHelper::generateActionSignature(['api_id' => 15, 'time' => time(), 'token'  => 'HSKSOOJH9762tSDSDF']),
            'time'   => time()
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success', 'user_id' => 1]);
    }

    public function testMethodGetBalance(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/casino/getbalance', [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'signature'  => \App\Components\Integrations\Casino\CasinoHelper::generateActionSignature(['api_id' => 15, 'time' => time(), 'token'  => 'HSKSOOJH9762tSDSDF']),
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
            'signature'  => \App\Components\Integrations\Casino\CasinoHelper::generateActionSignature(['api_id' => 15, 'time' => time(), 'token'  => 'HSKSOOJH9762tSDSDF']),
            'time'   => time()
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);
    }

    public function testMethodPayIn(ApiTester $I)
    {
        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => '121285348',
            'transaction_id' => random_int(90000, 250000),
            'amount' => 10,
            'time'   => time()
        ];

        $I->disableMiddleware();
        $I->sendPOST('/casino/payin', array_merge($request, [
            'signature'  => \App\Components\Integrations\Casino\CasinoHelper::generateActionSignature($request),
        ]));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);
    }

    public function testMethodPayOut(ApiTester $I)
    {
        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => '121285348',
            'transaction_id' => random_int(90000, 250000),
            'amount' => 10,
            'user_id' => 1,
            'time'   => time(),
            'type_operation' => 'rollback'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/casino/payout', array_merge($request, [
            'signature'  => \App\Components\Integrations\Casino\CasinoHelper::generateActionSignature($request),
        ]));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);
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