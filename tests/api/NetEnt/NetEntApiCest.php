<?php

namespace api\BetGames;

use App\Components\Integrations\NetEnt\CodeMapping;
use App\Components\Integrations\NetEnt\StatusCode;
use App\Components\Transactions\Strategies\NetEnt\ProcessNetEnt;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\GenericApiHttpException;
use App\Models\Transactions;
use Codeception\Scenario;
use \NetEnt\TestData;
use \NetEnt\TestUser;
use Symfony\Component\HttpFoundation\Response;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\GameSessionsMock;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class NetEntApiCest
{
    private $data;
    private $action;

    /** @var TestUser */
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData($this->testUser);
        $this->action = '/netent';
    }

    public function _before(\ApiTester $I, Scenario $s)
    {
        $I->disableMiddleware();
        if ($s->getFeature() != 'test ping') {
            $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
            $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
        }
    }

    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendPOST($this->action, $this->data->notFound());
        $this->getResponseFail($I, StatusCode::METHOD);
    }

    public function testPing(\ApiTester $I)
    {
        $I->sendPOST($this->action, $this->data->ping());
        $this->getResponseOk($I);
    }

    public function testBalance(\ApiTester $I)
    {
        $I->sendPOST($this->action, $this->data->getBalance());
        $data = $this->getResponseOk($I);
        $I->assertNotNull($data['balance']);
    }

    public function testBet(\ApiTester $I)
    {
        $balanceBefore = $this->testUser->getBalance();

        $request = $this->data->bet();

        $I->sendPOST($this->action, $request);
        $response = $this->getResponseOk($I, true);
        $I->assertNotNull($response['balance']);
        $I->assertEquals($balanceBefore - $this->data->getAmount(), $response['balance']);

        $this->isRecord($I, $request, 'bet');

        return $request;
    }

    public function testCurrencyBet(\ApiTester $I)
    {
        $request = $this->data->bet();
        $request['currency'] = 'QQ';
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I);
    }


    public function testExBet(\ApiTester $I)
    {
        $this->data->setAmount(1000000000000000);
        $request = $this->data->bet();
        $balanceBefore = $this->testUser->getBalance();

        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I, StatusCode::INSUFFICIENT_FUNDS);
        $I->assertEquals($balanceBefore, $this->testUser->getBalance());
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testDuplicateBet(\ApiTester $I)
    {
        $betData = $this->data->bet();
        $I->sendPOST($this->action, $betData);

        $balanceBefore = $this->testUser->getBalance();
        $request = $this->data->bet(null, $betData['tid']);

        $I->sendPOST($this->action, $request);
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balanceBefore, $response['balance']);
    }



    public function testWin(\ApiTester $I)
    {
        $bet = $this->data->bet();
        $I->sendPOST($this->action, $bet);

        $balanceBefore = $this->testUser->getBalance();
        $request = $this->data->win($bet['i_gameid']);
        $I->sendPOST($this->action, $request);
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balanceBefore + $this->data->getAmount(), $response['balance']);

        $this->isRecord($I, $request, 'win');

        return $request;
    }

    public function testDuplicateWin(\ApiTester $I)
    {
        $win = $this->testWin($I);

        $balanceBefore = $this->testUser->getBalance();
        $request = $this->data->win($win['i_gameid'], $win['tid']);

        $I->sendPOST($this->action, $request);
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balanceBefore, $response['balance']);
    }

    public function testRound(\ApiTester $I)
    {
        $request = $this->data->roundInfo();
        $I->sendPOST($this->action, $request);
        $this->getResponseOk($I);
    }

    public function testNoBet(\ApiTester $I)
    {
        $balanceBefore = $this->testUser->getBalance();
        $game_number = $this->getUniqueNumber();
        $request = $this->data->win($game_number);
        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I, StatusCode::BAD_OPERATION_ORDER);
        $I->assertEquals($balanceBefore, $this->testUser->getBalance());

        $this->noRecord($I, $request, 'win');
    }

    /** wrong data tests */
    public function testWrongHmac(\ApiTester $I)
    {
        $request = $this->data->ping();
        $request['hmac'] = 'qwerty';
        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I, StatusCode::HMAC);
    }

    public function testWrongParam(\ApiTester $I)
    {
        $request = $this->data->getBalance();
        $request['userid'] = 'qwerty';
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I);
    }

    private function transMismatch(\ApiTester $I, $request, $attr, $value)
    {
        $request[$attr] = $value;
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I, StatusCode::TRANSACTION_MISMATCH);
    }

    public function testMismatch(\ApiTester $I)
    {
        $request = $this->data->bet();
        $I->sendPOST($this->action, $request);

        $this->transMismatch($I, $request, 'userid', $request['userid'] + 1);
        $this->transMismatch($I, $request, 'currency', 'QQ');
        $this->transMismatch($I, $request, 'amount', $request['amount'] + 1);
    }

    /** fail in runtime */
    public function testFailPending(\ApiTester $I)
    {
        $mock = $this->mock(ProcessNetEnt::class);
        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);
        $mock->shouldReceive('runPending')->once()->withNoArgs()->andThrow(new GenericApiHttpException(500, $error['message'], [], null, [], $error['code']));
        $request = $this->data->bet();
        $I->sendPOST($this->action, $request);
        $this->getResponseFail($I, StatusCode::UNKNOWN, Response::HTTP_REQUEST_TIMEOUT);
        $this->noRecord($I, $request, 'bet');
    }

    public function testFailDb(\ApiTester $I)
    {
        $mock = $this->mock(ProcessNetEnt::class);
        $mock->shouldReceive('writeTransaction')->once()->withNoArgs()->andThrow(new \RuntimeException("", 500));
        $request = $this->data->bet();
        $I->sendPOST($this->action, $request);
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
        $I->assertEquals('OK', $data['status']);
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