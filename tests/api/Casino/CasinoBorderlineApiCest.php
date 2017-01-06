<?php

use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;

class CasinoBorderlineApiCest
{

    private $objectId;
    private $user_balance;
    private $options;

    public function _before()
    {
        $this->options = config('integrations.casino');
    }

    public function _after()
    {
    }

    public function testNoBetWin(ApiTester $I)
    {
        $this->objectId = random_int(100000, 9900000);

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
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => false, 'code' => 0]);

        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['transaction_id'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testStoragePending(ApiTester $I)
    {
        $this->objectId = random_int(100000, 9900000);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $this->objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => 10,
            'user_id' => env('TEST_USER_ID'),
            'time'   => time()
        ];

        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        Transactions::create([
            'operation_id' => app('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'transaction_id'),
            'object_id' => $this->objectId,
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['transaction_id'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/casino/payin', array_merge($request, [
            'signature'  => CasinoHelper::generateActionSignature($request),
        ]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->canSeeResponseContains("\"balance\"");
        $I->seeResponseContainsJson(['status' => true, 'message' => 'success']);

        $I->assertEquals([$testUser->getBalanceInCents() - 10], $I->grabDataFromResponseByJsonPath('balance'), "Balance does not match");
    }

    public function testZeroWin(ApiTester $I)
    {
        $this->objectId = random_int(100000, 9900000);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $this->objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => 10,
            'user_id' => env('TEST_USER_ID'),
            'time'   => time(),
            'type_operation' => 'win'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/casino/payout', array_merge($request, [
            'signature'  => CasinoHelper::generateActionSignature($request),
        ]));
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->expect('min required items in response');
        $I->seeResponseContainsJson(['status' => false, 'code' => 0]);

        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['transaction_id'],
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}