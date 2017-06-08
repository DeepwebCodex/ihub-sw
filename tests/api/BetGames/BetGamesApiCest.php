<?php

namespace api\BetGames;

use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\StatusCode;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use App\Components\Transactions\Strategies\BetGames\ProcessBetGames;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use \BetGames\TestData;
use \BetGames\TestUser;
use Codeception\Scenario;
use Testing\DriveMedia\AccountManagerMock;
use Testing\DriveMedia\Params;
use Testing\GameSessionsMock;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class BetGamesApiCest
{
    const OFFLINE = [
        'test token',
        'test method not found',
        'test ping',
        'test win',
    ];

    private $data;

    /** @var TestUser */
    private $testUser;

    /** @var Params  */
    private $params;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->params = new Params('betGames');
        $this->data = new TestData($this->params);
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
        $I->sendPOST('/bg/favbet/', $this->data->notFound());
        $this->getResponseFail($I, StatusCode::UNKNOWN);

    }

    public function testPing(\ApiTester $I)
    {
        $I->sendPOST('/bg/favbet/', $this->data->ping());
        $this->getResponseOk($I);
    }

    private function mock($class)
    {
        $mock = \Mockery::mock($class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial()
        ;
        app()->instance($class, $mock);
        return $mock;
    }

    public function testFailAuth(\ApiTester $I)
    {
        $I->sendPOST('/bg/favbet/', $this->data->authFailed());
        $this->getResponseFail($I, StatusCode::TOKEN);
    }

    public function testFailPending(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);

        $mock = $this->mock(ProcessBetGames::class);
        $mock->shouldReceive('runPending')->once()->withNoArgs()->andThrow(new GenericApiHttpException(500, $error['message'], [], null, [], $error['code']));

        $request = $this->data->bet(100);
        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::UNKNOWN);
        $this->noRecord($I, $request, 'bet');
    }

    public function testFailCompleted(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $mock = $this->mock(ProcessBetGames::class);
        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);
        $mock->shouldReceive('runCompleted')->once()->withAnyArgs()->andThrow(new GenericApiHttpException(500, $error['message'], [], null, [], $error['code']));

        $bet = 100;
        $request = $this->data->bet($bet);
        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::UNKNOWN);
        $this->noRecord($I, $request, 'bet');
    }

    public function testFailDb(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $mock = $this->mock(ProcessBetGames::class);
        $mock->shouldReceive('writeTransaction')->once()->withNoArgs()->andThrow(new \RuntimeException("", 500));
        $request = $this->data->bet(1);
        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::UNKNOWN);
        $this->noRecord($I, $request, 'bet');
    }

    public function testAccount(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $I->sendPOST('/bg/favbet/', $this->data->account());
        $response = $this->getResponseOk($I);
        $I->assertEquals($this->params->userId, $response['params']['user_id']);
    }

    public function testRefreshToken(\ApiTester $I)
    {
        $request = $this->data->refreshToken();
        $I->sendPOST('/bg/favbet/', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals($request['token'], $response['token']);
    }

    public function testNewToken(\ApiTester $I)
    {
        $request = $this->data->newToken();
        $I->sendPOST('/bg/favbet/', $request);
        $response = $this->getResponseOk($I);

        $I->assertEquals($request['token'], $response['token']);
        $I->assertNotEquals($request['token'], $response['params']['new_token']);
    }

    public function testGetBalance(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        (new AccountManagerMock($this->params))->mock($I);

        $request = $this->data->getBalance();
        $I->sendPOST('/bg/favbet/', $request);
        $response = $this->getResponseOk($I);
        $I->assertNotNull($response['params']['balance']);
        $I->assertEquals(100 * $balance, $response['params']['balance']);
    }

    public function testBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 3;
        $request = $this->data->bet($bet);

        (new AccountManagerMock($this->params))->bet($request['params']['bet_id'], $bet/100, $balance - $bet/100)->mock($I);

        $I->sendPOST('/bg/favbet/', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals((int)(100 * $balance) - $bet, $response['params']['balance_after']);

        $this->isRecord($I, $request, 'bet');

        return $request;
    }

    public function testWin(\ApiTester $I)
    {
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());

        $balance = $this->params->getBalance();
        $bet = 3;
        $win = 4;
        $betRequest = $this->data->bet($bet);

        (new AccountManagerMock($this->params))
            ->bet($betRequest['params']['bet_id'], $bet/100, $balance - $bet/100)
            ->win($betRequest['params']['bet_id'], $win/100, $balance - $bet/100 + $win/100)
            ->mock($I);

        $I->sendPOST('/bg/favbet/', $betRequest);

        $winRequest = $this->data->win($win, $betRequest['params']['bet_id']);

        $I->sendPOST('/bg/favbet/', $winRequest);
        $response = $this->getResponseOk($I);
        $I->assertEquals((int)(100 * $balance) - $bet + $win, $response['params']['balance_after']);

        $this->isRecord($I, $winRequest, 'win');

        return $winRequest;
    }

    public function testDuplicateBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet1 = 3;
        $bet2 = 4;
        $betRequest = $this->data->bet($bet1);

        (new AccountManagerMock($this->params))
            ->bet($betRequest['params']['bet_id'], $bet1/100, $balance - $bet1/100)
            ->mock($I);

        $I->sendPOST('/bg/favbet/', $betRequest);

        $betRequest2 = $this->data->bet($bet2, null, $betRequest['params']['transaction_id']);
        $I->sendPOST('/bg/favbet/', $betRequest2);

        $response = $this->getResponseOk($I);
        $I->assertEquals(1, $response['params']['already_processed']);
        //TODO: balance for mock
//        $I->assertEquals(100 * ($balance - $bet1/100), $response['params']['balance_after']);
    }

    public function testDuplicateWin(\ApiTester $I)
    {
        $win = $this->testWin($I);
        $amount = 4;
        $request = $this->data->win($amount, $win['params']['bet_id'], $win['params']['transaction_id']);

        $I->sendPOST('/bg/favbet/', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals(1, $response['params']['already_processed']);
        //TODO: balance for mock
//        $I->assertEquals($balanceBefore, $response['params']['balance_after']);
    }

    public function testNoBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();
        $bet = 3;
        $win = 4;
        $betRequest = $this->data->bet($bet);

        (new AccountManagerMock($this->params))
            ->bet($betRequest['params']['bet_id'], $bet/100, $balance - $bet/100)
            ->win($betRequest['params']['bet_id'], $win/100, $balance - $bet/100 + $win/100)
            ->mock($I);

        $balanceBefore = $this->testUser->getBalanceInCents();
        $request = $this->data->win($win, $betRequest['params']['bet_id']);
        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::BAD_OPERATION_ORDER);
        $I->assertEquals($balanceBefore, $this->testUser->getBalanceInCents());

        $this->noRecord($I, $request, 'win');
    }

    public function testMultiWin(\ApiTester $I)
    {
        $win1Request = $this->testWin($I);
        $balance = $this->params->getBalance();
        $amount = 10;
        $request = $this->data->win($amount, $win1Request['params']['bet_id']);

        (new AccountManagerMock($this->params))
            ->win($win1Request['params']['bet_id'], 1/100, $balance)
            ->mock($I);

        $I->sendPOST('/bg/favbet/', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals(1, $response['params']['already_processed']);
        $I->assertEquals((int)(100 * $balance), $response['params']['balance_after']);

        //TODO: fix it
//        $this->noRecord($I, $request, 'win');
    }

    public function testExBet(\ApiTester $I)
    {
        $bet = 100000000000000;
        $request = $this->data->bet($bet);

        (new AccountManagerMock($this->params))
            ->betExceeded($request['params']['bet_id'], $bet/100)
            ->mock($I);

        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::INSUFFICIENT_FUNDS);
        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroBet(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $request = $this->data->bet(0);
        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::SIGNATURE);

        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroWin(\ApiTester $I)
    {
        $bet = 3;
        $balance = $this->params->getBalance();
        $betRequest = $this->data->bet($bet);

        (new AccountManagerMock($this->params))
            ->bet($betRequest['params']['bet_id'], $bet/100, $balance - $bet/100)
            ->mock($I);

        // bet
        $I->sendPOST('/bg/favbet/', $betRequest);

        // win
        $winAmount = 0;
        $request = $this->data->win($winAmount, $betRequest['params']['bet_id']);
        $I->sendPOST('/bg/favbet/', $request);

        $this->getResponseOk($I);
        $this->isRecord($I, $request, 'win');
    }

    /*** test validation ***/

    public function testWrongAmountBet(\ApiTester $I)
    {
        $bet = -100;
        $request = $this->data->bet($bet);

        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testWrongSignature(\ApiTester $I)
    {
        $data = $this->data->bet(1);
        $data['signature'] = '123';
        $I->sendPOST('/bg/favbet/', $data);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
    }

    public function testWrongTime(\ApiTester $I)
    {
        $data = $this->data->wrongTime('get_balance');
        $I->sendPOST('/bg/favbet/', $data);
        $this->getResponseFail($I, StatusCode::TIME);
    }

    public function testWrongParams(\ApiTester $I)
    {
        $data = $this->data->bet(1);
        unset($data['params']['amount']);
        $I->sendPOST('/bg/favbet/', $data);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
    }

    public function testWrongToken(\ApiTester $I)
    {
        $data = $this->data->getBalance();
        $data['token'] = '123';
        $request = $this->data->updateSignature($data);

        $I->sendPOST('/bg/favbet/', $request);
        $this->getResponseFail($I, StatusCode::TOKEN);
    }

    /*public function testToken(\ApiTester $I)
    {
        $data = $this->data->token();
        $I->sendPOST('/bg/favbet/', $data);
        $response = $this->getResponseOk($I);
        $I->assertNotNull($response['params']['new_token']);
    }*/

    private function execBet(\ApiTester $I)
    {
        $bet = 3;
        $request = $this->data->bet($bet);
        $I->sendPOST('/bg/favbet/', $request);

        return $request;
    }

    private function getResponseOk(\ApiTester $I)
    {
        $I->seeResponseCodeIs(200);

        $data = $this->responseToArray($I);

        $signatureValidation = $this->validateSignature(
            array_get($data, 'signature'),
            $data,
            $this->data->partnerId,
            $this->data->cashdeskId
        );

        $I->assertTrue($signatureValidation);

        $I->assertArrayHasKey('method', $data);
        $I->assertArrayHasKey('token', $data);
        $I->assertArrayHasKey('success', $data);
        $I->assertArrayHasKey('error_code', $data);
        $I->assertArrayHasKey('error_text', $data);
        $I->assertArrayHasKey('params', $data);
        if (in_array($data['method'], ['transaction_bet_payin', 'transaction_bet_payout'])) {
            $I->assertArrayHasKey('already_processed', $data['params']);
            $I->assertArrayHasKey('balance_after', $data['params']);
            $I->assertNotNull($data['params']['balance_after']);
            $I->assertNotNull($data['params']['already_processed']);
        }
        $I->assertEquals(1, $data['success']);

        return $data;
    }

    private function getResponseFail(\ApiTester $I, $errorCode)
    {
        $data = $this->responseToArray($I);

        $signatureValidation = $this->validateSignature(
            array_get($data, 'signature'),
            $data,
            $this->data->partnerId,
            $this->data->cashdeskId
        );

        $I->assertTrue($signatureValidation);

        $I->assertArrayHasKey('method', $data);
        $I->assertArrayHasKey('token', $data);
        $I->assertArrayHasKey('success', $data);
        $I->assertArrayHasKey('error_code', $data);
        $I->assertArrayHasKey('error_text', $data);
        $I->assertEquals(0, $data['success']);
        $error = CodeMapping::getByErrorCode($errorCode);
        $I->assertEquals($error['code'], $data['error_code']);

        return $data;
    }

    private function isRecord(\ApiTester $I, $request, $method)
    {
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['params']['transaction_id'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function noRecord(\ApiTester $I, $request, $method)
    {
        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(Transactions::class, [
            'foreign_id' => $request['params']['transaction_id'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function responseToArray(\ApiTester $I)
    {
        $xml = new \SimpleXMLElement($I->grabResponse());
        $result = json_decode(json_encode((array)$xml), 1);

        if (isset($result['error_text']) && is_array($result['error_text']) && count($result['error_text']) === 0){
            $result['error_text'] = '';
        }

        return $result;
    }

    private function validateSignature($signature, $data, $partnerId, $cashdeskId)
    {
        unset($data['signature']);

        $generatedSignature = new Signature($data, $partnerId, $cashdeskId);
        $this->signature = $generatedSignature->getHash();
        return !$generatedSignature->isWrong($signature);
    }
}