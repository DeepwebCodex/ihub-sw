<?php

namespace api\WirexGaming;

use App\Components\Integrations\GameSession\GameSessionService;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Testing\DriveMedia\AccountManagerMock;
use Testing\DriveMedia\Params;
use Testing\GameSessionsMock;
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

    /** @var Params  */
    private $params;

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
        $this->params = new Params('wirexGaming');
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
        (new AccountManagerMock($this->params))->mock($I);

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
        (new AccountManagerMock($this->params))->mock($I);

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
        $transactionUid = $this->data->makeTransactionUid();
        $amount = 1;
        $request = $this->data->addWithdrawEntry($transactionUid, $amount);
        (new AccountManagerMock($this->params))->bet($transactionUid, $amount)->mock($I);

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

    public function testRollbackWithdraw(\ApiTester $I)
    {
        // bet
        $betTransactionUid = $this->data->makeTransactionUid();
        $amount = 1;
        $requestBet = $this->data->addWithdrawEntry($betTransactionUid, $amount);
        (new AccountManagerMock($this->params))
            ->bet($betTransactionUid, $amount)
            ->win($betTransactionUid, $amount)
            ->mock($I);

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $requestBet);

        // rollback
        $rollbackTransactionUid = $this->data->makeTransactionUid();
        $request = $this->data->rollbackWithdraw($rollbackTransactionUid, $betTransactionUid, $amount);

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $rollbackTransactionUid,
            'transaction_type' => TransactionRequest::TRANS_REFUND,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testAddDepositEntry(\ApiTester $I)
    {
        // bet
        $betTransactionUid = $this->data->makeTransactionUid();
        $amount = 1;
        $requestBet = $this->data->addWithdrawEntry($betTransactionUid, $amount);
        (new AccountManagerMock($this->params))
            ->bet($betTransactionUid, $amount)
            ->win($betTransactionUid, $amount)
            ->mock($I);

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $requestBet);

        // win
        $winTransactionUid = $this->data->makeTransactionUid();
        $winAmount = 1;
        $request = $this->data->addDepositEntry($winTransactionUid, $betTransactionUid, $winAmount);

        $I->disableMiddleware();
        $I->sendPOST('/wirex', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->seeXmlResponseIncludes('<status>OK</status>');
        $I->seeXmlResponseIncludes('<code>0</code>');

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $winTransactionUid,
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}
