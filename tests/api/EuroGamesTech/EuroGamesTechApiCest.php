<?php

namespace api\EuroGamesTech;

use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use \EuroGamesTech\TestData;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\GameSessionsMock;

class EuroGamesTechApiCest
{
    private $defenceCode;
    private $data;
    /**
     * @var IntegrationUser
     */
    private $testUser;

    public function __construct()
    {
        $this->data = new TestData();
    }

    public function _before(\ApiTester $I)
    {
        $I->disableMiddleware();
        $I->mockAccountManager($I, config('integrations.egt.service_id'));

        $this->testUser = IntegrationUser::get(env('TEST_USER_ID'), config('integrations.egt.service_id'), 'egt');
        $I->getApplication()
            ->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class,
            GameSessionsMock::getMock());
    }

    // tests
    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendGET('/egt');
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('both items are in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Server error</ErrorMessage>");
    }

    public function testMethodAuthenticate(\ApiTester $I)
    {
        $request = $this->data->authenticate();

        $this->dataAuthenticate($I, $request);
    }

    public function testMethodAuthenticateCompoundId(\ApiTester $I)
    {
        $request = $this->data->authenticate(false);

        $this->dataAuthenticate($I, $request);
    }

    protected function dataAuthenticate(\ApiTester $I, $request)
    {
        $this->defenceCode = md5(uniqid('egt' . random_int(-99999, 999999)));
        $I->sendPOST('/egt/Authenticate',
            array_merge($request, ['DefenceCode' => $this->defenceCode]));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");
    }

    public function testMethodGetPlayerBalance(\ApiTester $I)
    {
        $request = $this->data->getBalance();

        $this->dataGetPlayerBalance($I, $request);
    }

    public function testMethodGetPlayerBalanceCompoundId(\ApiTester $I)
    {
        $request = $this->data->getBalance(false);

        $this->dataGetPlayerBalance($I, $request);
    }

    private function dataGetPlayerBalance(\ApiTester $I, $request)
    {
        $I->sendPOST('/egt/GetPlayerBalance', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");
    }

    public function testMethodWithdraw(\ApiTester $I)
    {
        $request = $this->data->bet();

        $this->dataWithdraw($I, $request);
    }

    public function testMethodWithdrawCompoundId(\ApiTester $I)
    {
        $request = $this->data->bet(false);

        $this->dataWithdraw($I, $request);
    }

    private function dataWithdraw(\ApiTester $I, $request)
    {
        $balance = $this->testUser->getBalanceInCents();

        $I->sendPOST('/egt/Withdraw', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $expectedBalance = $balance - $this->data->getAmount();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id'       => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status'           => TransactionRequest::STATUS_COMPLETED,
            'move'             => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testMethodDeposit(\ApiTester $I)
    {
        $request = $this->data->bet();
        $I->sendPOST('/egt/Withdraw', $request);

        $request2 = $this->data->win($request['GameNumber']);
        $this->dataDeposit($I, $request2);
    }

    public function testMethodDepositCompoundId(\ApiTester $I)
    {
        $request = $this->data->bet(false);
        $this->dataWithdraw($I, $request);
        $request = $this->data->win($request['GameNumber'], false);

        $this->dataDeposit($I, $request);
    }

    private function dataDeposit(\ApiTester $I, $request)
    {
        $balance = $this->testUser->getBalanceInCents();

        $I->sendPOST('/egt/Deposit', $request);

        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $expectedBalance = $balance + $this->data->getAmount();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id'       => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status'           => TransactionRequest::STATUS_COMPLETED,
            'move'             => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testWithdrawAndDepositCompoundId(\ApiTester $I)
    {
        $request = $this->data->betWin(false);

        $this->dataWithdrawAndDeposit($I, $request);
    }

    private function dataWithdrawAndDeposit(\ApiTester $I, $request)
    {
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
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id'       => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status'           => TransactionRequest::STATUS_COMPLETED,
            'move'             => TransactionRequest::D_DEPOSIT
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id'       => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status'           => TransactionRequest::STATUS_COMPLETED,
            'move'             => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testWithdrawAndDepositLost(\ApiTester $I)
    {
        $request = $this->data->betLost(false);

        $this->dataWithdrawAndDepositLost($I, $request);
    }

    public function testWithdrawAndDepositLostCompoundId(\ApiTester $I)
    {
        $request = $this->data->betLost(false);

        $this->dataWithdrawAndDepositLost($I, $request);
    }

    private function dataWithdrawAndDepositLost(\ApiTester $I, $request)
    {
        $expectedBalance = $this->testUser->getBalanceInCents()
            - $this->data->getAmount();

        $I->sendPOST('/egt/WithdrawAndDeposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->expect('unchanged balance after operation');
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of both transactions applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id'       => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status'           => TransactionRequest::STATUS_COMPLETED,
            'move'             => TransactionRequest::D_DEPOSIT
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id'       => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status'           => TransactionRequest::STATUS_COMPLETED,
            'move'             => TransactionRequest::D_WITHDRAWAL
        ]);
    }
}