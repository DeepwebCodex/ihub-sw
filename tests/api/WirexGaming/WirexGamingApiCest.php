<?php

namespace api\WirexGaming;

use App\Components\Integrations\GameSession\GameSessionService;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Testing\GameSessionsMock;
use Testing\Params;
use WirexGaming\TestData;

/**
 * Class WirexGamingApiCest
 * @package api\WirexGaming
 */
class WirexGamingApiCest
{
    /**
     * @var TestData
     */
    private $data;

    private $betTransactionUid;

    /**
     * @param $oid
     * @return int
     */
    protected static function makeUid($oid)
    {
        $previousContextId = config('integrations.wirexGaming.previous_context_id');
        return ($oid << 16) + $previousContextId;
    }

    public function _before(\ApiTester $I)
    {
        $this->data = new TestData();
        $I->mockAccountManager($I, config('integrations.wirexGaming.service_id'));
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendGET('/wirex');
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('both items are in response');
        $I->seeXmlResponseIncludes('<message>Unknown method</message>');
        $I->seeXmlResponseIncludes('<status>ERROR</status>');
    }

    public function testGetPersistentSession(\ApiTester $I)
    {
        $request = $this->data->getPersistentSession();
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

        $request = $this->data->getUserData();

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');
    }

    public function testGetAvailableBalance(\ApiTester $I)
    {
        $request = $this->data->getAvailableBalance();

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');
    }

    public function testAddWithdrawEntry(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        //$transactionUid = $this->data->makeTransactionUid();
        $transactionUid = Params::OBJECT_ID;

        $request = $this->data->addWithdrawEntry();

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $transactionUid,
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    /**
     * @param \ApiTester $I
     * @skip
     */
    public function testRollbackWithdraw(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        //$transactionUid = $this->data->makeTransactionUid();
        $transactionUid = Params::OBJECT_ID;

        $request = $this->data->rollbackWithdraw($this->betTransactionUid);

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $transactionUid,
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testAddDepositEntry(\ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $this->testAddWithdrawEntry($I);

        //$transactionUid = $this->data->makeTransactionUid();
        $transactionUid = Params::OBJECT_ID;

        $request = $this->data->addDepositEntry($this->betTransactionUid);

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $transactionUid,
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}
