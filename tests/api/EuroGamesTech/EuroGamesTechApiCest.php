<?php

namespace api\EuroGamesTech;

use App\Components\Transactions\TransactionRequest;
use \App\Components\Integrations\EuroGamesTech\StatusCode;
use \App\Components\Integrations\EuroGamesTech\DefenceCode;
use \EuroGamesTech\TestData;
use \EuroGamesTech\TestUser;

class EuroGamesTechApiCest
{

    private $gameNumber;
    private $defenceCode;
    private $data;
    /**
     * @var TestUser
     */
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData($this->testUser);
    }

    public function _before()
    {

    }

    public function _after()
    {
    }

    // tests
    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendGET('/egt');
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsXml();
        $I->expect('both items are in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Server error</ErrorMessage>");
    }

    public function testMethodAuthenticate(\ApiTester $I)
    {
        $request = $this->data->authenticate();

        $I->disableMiddleware();
        $this->defenceCode = (new DefenceCode())->generate($request['PlayerId'], $request['PortalCode'], time());
        $I->sendPOST('/egt/Authenticate', array_merge($request, ['DefenceCode' => $this->defenceCode]));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");
    }

    /**
     * @depends testMethodAuthenticate
     * @param \ApiTester $I
     */
    public function testDefenceCodeDuplicate(\ApiTester $I)
    {
        $request = $this->data->authenticate();

        $I->disableMiddleware();
        $I->sendPOST('/egt/Authenticate', array_merge($request, ['DefenceCode' => $this->defenceCode]));
        $response = (array)(new \SimpleXMLElement($I->grabResponse()));
        $I->assertEquals(StatusCode::EXPIRED, $response['ErrorCode']);
    }

    public function testMethodGetPlayerBalance(\ApiTester $I)
    {
        $request = $this->data->getBalance();

        $I->disableMiddleware();
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
        $balance = $this->testUser->getBalanceInCents();
        $request = $this->data->bet();
        $this->gameNumber = $request['GameNumber'];

        $I->disableMiddleware();
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
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testMethodDeposit(\ApiTester $I)
    {
        $this->testMethodWithdraw($I);
        $balance = $this->testUser->getBalanceInCents();
        $request = $this->data->win($this->gameNumber);

        $I->disableMiddleware();
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
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testWithdrawAndDeposit(\ApiTester $I)
    {
        $request = $this->data->betWin();

        $I->disableMiddleware();
        $I->sendPOST('/egt/WithdrawAndDeposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->expect('unchanged balance after operation');
        $expectedBalance = $this->testUser->getBalanceInCents();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of both transactions applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testJackpot(\ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/internal/egt/jackpot/set');
        $I->sendPOST('/internal/egt/jackpot/get');
        $I->seeResponseIsJson();
    }
}