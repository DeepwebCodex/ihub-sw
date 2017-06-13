<?php

namespace api\Fundist;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Integrations\LiveDealer\ObjectId;
use App\Components\Transactions\Strategies\Fundist\ProcessFundist;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Codeception\Scenario;
use \Fundist\TestData;
use Symfony\Component\HttpFoundation\Response;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use Testing\GameSessionsMock;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class LiveDealerApiCest
{
    /** @var  TestData */
    private $data;
    private $action;

    /** @var Params  */
    private $params;

    const OFFLINE = [
        'test ping',
        'test fail auth',
    ];

    public function __construct()
    {
        $this->data = new TestData('liveDealer');
        $this->action = '/livedealer';
        $this->params = new Params('liveDealer');
    }

    public function _before(\ApiTester $I, Scenario $s)
    {
        if (!in_array($s->getFeature(), self::OFFLINE)) {
            $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
            $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
        }
    }

    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendPOST($this->action, json_encode($this->data->notFound()));
        $this->getResponseFail($I, StatusCode::METHOD);
    }

    public function testPing(\ApiTester $I)
    {
        $I->sendPOST($this->action, json_encode($this->data->ping()));
        $this->getResponseOk($I);
    }

    public function testBalance(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $I->sendPOST($this->action, json_encode($this->data->getBalance()));
        $data = $this->getResponseOk($I);
        $I->assertNotNull($data['balance']);
    }

    public function testBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 3;
        $request = $this->data->bet($bet);
        $objectId = ObjectId::get($request['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($request));
        $response = $this->getResponseOk($I, true);
        $I->assertNotNull($response['balance']);
        $I->assertEquals($balance - $bet, $response['balance']);

        $this->isRecord($I, $request, 'bet');

        return $request;
    }

    public function testCurrencyBet(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $request = $this->data->bet();
        $request['currency'] = 'QQ';
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I);
    }


    public function testExBet(\ApiTester $I)
    {
        $bet = 100000000000000;
        $request = $this->data->bet($bet);
        $objectId = ObjectId::get($request['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->betExceeded($objectId, $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::INSUFFICIENT_FUNDS);

        $this->noRecord($I, $request, 'bet');
    }

    public function testFailAuth(\ApiTester $I)
    {
        $I->sendPOST($this->action, json_encode($this->data->authFailed()));
        $this->getResponseFail($I, StatusCode::TOKEN);
    }

    public function testZeroBet(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $request = $this->data->bet(0.00);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I);

        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroWin(\ApiTester $I)
    {
        $bet = 3;
        $balance = $this->params->getBalance();
        $betRequest = $this->data->bet($bet);
        $objectId = ObjectId::get($betRequest['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        // bet
        $I->sendPOST($this->action, json_encode($betRequest));

        //win
        $winRequest = $this->data->win(0.00, $betRequest['i_actionid']);
        $I->sendPOST($this->action, json_encode($winRequest));
        $this->getResponseOk($I, true);

        $this->isRecord($I, $winRequest, 'win');
    }

    public function testDuplicateBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 1;
        $betRequest1 = $this->data->bet($bet);
        $objectId = ObjectId::get($betRequest1['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet)
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($betRequest1));

        $betRequest2 = $this->data->bet($bet, null, $betRequest1['tid']);
        $I->sendPOST($this->action, json_encode($betRequest2));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balance - $bet, $response['balance']);
    }



    public function testWin(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 3;
        $win = 4;
        $betRequest = $this->data->bet($bet);
        $objectId = ObjectId::get($betRequest['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->win($objectId, $win, $balance - $bet + $win)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($betRequest));

        $winRequest = $this->data->win($win, $betRequest['i_actionid']);

        $I->sendPOST($this->action, json_encode($winRequest));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balance - $bet + $win, $response['balance']);

        $this->isRecord($I, $winRequest, 'win');

        return $winRequest;
    }

    public function testDuplicateWin(\ApiTester $I)
    {
        $win = $this->testWin($I);
        $balance = $this->params->getBalance();
        $amount = 4;
        $request = $this->data->win($amount, $win['i_actionid'], $win['tid']);

        $I->sendPOST($this->action, json_encode($request));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balance, $response['balance']);
    }

    public function testRound(\ApiTester $I)
    {
        $request = $this->data->roundInfo();
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseOk($I);
    }

    public function testNoBet(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $game_number = $this->getUniqueNumber();
        $request = $this->data->win(1, $game_number);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::BAD_OPERATION_ORDER);

        $this->noRecord($I, $request, 'win');
    }

    /** wrong data tests */
    public function testWrongHmac(\ApiTester $I)
    {
        $request = $this->data->ping();
        $request['hmac'] = 'qwerty';
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::HMAC);
    }

    public function testWrongParam(\ApiTester $I)
    {
        $request = $this->data->getBalance();
        $request['currency'] = 'q';
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I);
    }

    private function transMismatch(\ApiTester $I, $request, $attr, $value)
    {
        $request[$attr] = $value;
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::TRANSACTION_MISMATCH);
    }

    public function testMismatch(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 3;
        $request = $this->data->bet($bet);
        $objectId = ObjectId::get($request['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($request));

        $this->transMismatch($I, $request, 'userid', time() . '_' . $this->params->currency);
        $this->transMismatch($I, $request, 'currency', 'QQ');
        $this->transMismatch($I, $request, 'amount', $request['amount'] + 1);
    }

    /** fail in runtime */
    public function testFailPending(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $mock = $this->mock(ProcessFundist::class);
        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);
        $mock->shouldReceive('runPending')->once()->withNoArgs()->andThrow(new GenericApiHttpException(500, $error['message'], [], null, [], $error['code']));
        $request = $this->data->bet(1);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::UNKNOWN, Response::HTTP_REQUEST_TIMEOUT);
        $this->noRecord($I, $request, 'bet');
    }

    public function testFailDb(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 3;
        $request = $this->data->bet($bet);
        $objectId = ObjectId::get($request['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $mock = $this->mock(ProcessFundist::class);
        $mock->shouldReceive('writeTransaction')->once()->withNoArgs()->andThrow(new \RuntimeException("", 500));
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::UNKNOWN, Response::HTTP_REQUEST_TIMEOUT);
        $this->noRecord($I, $request, 'bet');
    }

    protected function getUniqueNumber()
    {
        return time() + mt_rand(1, 10000);
    }

    private function mock($class)
    {
        $mock = \Mockery::mock($class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        app()->instance($class, $mock);
        return $mock;
    }


    private function getResponseOk(\ApiTester $I, $isTransaction = false)
    {
        $I->seeResponseCodeIs(200);
        $data = $this->responseToArray($I);
        $I->assertEquals('OK', array_get($data, 'status'));
        $I->assertNotNull($data['hmac']);
        if ($isTransaction) {
            //$I->assertArrayHasKey('balance', $data['balance']);
            $I->assertNotNull($data['balance']);
            $I->assertNotNull($data['tid']);
        }

        return $data;
    }

    private function getResponseFail(\ApiTester $I, $errorCode=null, $httpCode = 200)
    {
        $I->seeResponseCodeIs($httpCode);
        $data = $this->responseToArray($I);
        $I->assertNotNull($data['error']);
        $I->assertNotNull($data['hmac']);
        if($errorCode) {
            $error = CodeMapping::getByErrorCode($errorCode);
            $I->assertEquals($error['message'], $data['error']);
        }

        return $data;
    }

    private function isRecord(\ApiTester $I, $request, $method)
    {
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['tid'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function noRecord(\ApiTester $I, $request, $method)
    {
        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(Transactions::class, [
            'foreign_id' => $request['tid'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function responseToArray(\ApiTester $I)
    {
        return json_decode($I->grabResponse(), true);
    }
}