<?php

namespace api\EuroGamesTech;

use App\Components\Integrations\GameSession\GameSessionService;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use \EuroGamesTech\TestData;
use \EuroGamesTech\TestUser;
use Testing\GameSessionsMock;


class EuroGamesTechBorderlineApiCest
{

    private $options;
    private $data;
    /**
     * @var TestUser
     */
    private $testUser;

    public function __construct()
    {
        $this->data = new TestData();
    }

    public function _before(\ApiTester $I)
    {
        $this->options = config('integrations.egt');
        $I->disableMiddleware();

        if(env('ENABLE_ACCOUNT_MANAGER_MOCK') ?? true) {
            $I->mockAccountManager($I, config('integrations.egt.service_id'));
        }

        $this->testUser = IntegrationUser::get(env('TEST_USER_ID'), config('integrations.egt.service_id'), 'egt');
        $I->getApplication()
            ->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class,
            GameSessionsMock::getMock());
    }

    public function _after()
    {
    }

    // tests
    public function testNoBetWin(\ApiTester $I)
    {
        $request = $this->data->win();

        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Bet was not placed</ErrorMessage>");

        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testStoragePending(\ApiTester $I)
    {
        $request = $this->data->bet();

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => env('TEST_PARTNER_ID'),
            'cashdesk' => env('TEST_CASHEDESK'),
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $this->testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => $request['GameNumber'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->sendPOST('/egt/Withdraw', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testTransactionDuplicate(\ApiTester $I)
    {
        $request = $this->data->bet();

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => env('TEST_PARTNER_ID'),
            'cashdesk' => env('TEST_CASHEDESK'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $this->testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => $request['GameNumber'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->sendPOST('/egt/Withdraw', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1100</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Transaction duplicate</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");
    }

    public function testZeroWin(\ApiTester $I)
    {
        $bet = $this->data->bet();
        $I->sendPOST('/egt/Withdraw', $bet);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), config('integrations.egt.service_id'), 'egt');
        $request = $this->data->win($bet['GameNumber']);
        $request['Amount'] = 0;

        /*Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => env('TEST_PARTNER_ID'),
            'cashdesk' => env('TEST_CASHEDESK'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $this->testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => $request['GameNumber'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'game_id'       => 0
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);*/

        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$testUser->getBalanceInCents()}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testMultiWin(\ApiTester $I)
    {
        $request = $this->data->betWin();
        $balanceBefore = $this->testUser->getBalanceInCents();


        $I->sendPOST('/egt/WithdrawAndDeposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->expect('unchanged balance after operation');
        $expectedBalance = $balanceBefore - $request['Amount'] + $request['WinAmount'];
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of both transactions applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);

        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $this->data->setAmount($this->data->jackpotAmount);
        $request = $this->data->win($request['GameNumber']);
        $request['Reason'] = 'JACKPOT_END';

        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");

        $expectedBalance += $this->data->getAmount();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BONUS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}