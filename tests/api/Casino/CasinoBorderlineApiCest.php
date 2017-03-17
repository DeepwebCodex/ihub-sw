<?php
namespace api\Casino;

use App\Components\ExternalServices\AccountManager;
use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Models\Transactions;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\Casino\AccountManagerMock;
use Testing\Casino\Params;
use Testing\GameSessionsMock;

class CasinoBorderlineApiCest
{
    private $options;
    private $params;

    public function __construct()
    {
        $this->params = new Params();
    }

    public function _before(\ApiTester $I)
    {
        $this->options = config('integrations.casino');

        if($this->params->enableMock) {
            $mock = (new AccountManagerMock())->getMock();
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }

        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function testNoBetWin(\ApiTester $I)
    {
        $objectId = $this->params->getObjectId(Params::NO_BET_OBJECT_ID);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => Params::AMOUNT * 100,
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

    public function testStoragePending(\ApiTester $I)
    {
        $objectId = $this->params->getObjectId(Params::STORAGE_PENDING_OBJECT_ID);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => Params::AMOUNT * 100,
            'user_id' => env('TEST_USER_ID'),
            'time'   => time()
        ];

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        Transactions::create([
            'operation_id' => app('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => Params::AMOUNT,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => env('TEST_PARTNER_ID'),
            'cashdesk' => env('TEST_CASHEDESK'),
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'transaction_id'),
            'object_id' => $objectId,
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'   => 0
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

        $I->assertEquals([$testUser->getBalanceInCents() - Params::AMOUNT * 100], $I->grabDataFromResponseByJsonPath('balance'), "Balance does not match");
    }

    public function testZeroWin(\ApiTester $I)
    {
        $objectId = $this->params->getObjectId(Params::ZERO_WIN_OBJECT_ID);

        $request = [
            'api_id' => 15,
            'token'  => 'HSKSOOJH9762tSDSDF',
            'object_id' => $objectId,
            'transaction_id' => random_int(90000, 250000),
            'amount' => Params::AMOUNT * 100,
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