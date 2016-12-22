<?php

namespace api\BetGames;

//use App\Components\Integrations\BetGames\Error;
use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;
use \BetGames\TestData;
use \BetGames\TestUser;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class BetGamesApiCest
{
    private $data;

    /** @var TestUser */
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData($this->testUser);
    }

    public function _before(\ApiTester $I)
    {
        $I->disableMiddleware();
    }

    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendPOST('/bg', $this->data->notFound());
        $this->getResponseFail($I, StatusCode::SIGNATURE);

    }

    public function testAuth(\ApiTester $I)
    {
        $I->sendPOST('/bg', $this->data->authFailed());
        $this->getResponseFail($I, StatusCode::TOKEN);
    }

    public function testPing(\ApiTester $I)
    {
        $I->sendPOST('/bg', $this->data->ping());
        $this->getResponseOk($I);
    }

    public function testAccount(\ApiTester $I)
    {
        $I->sendPOST('/bg', $this->data->account());
        $response = $this->getResponseOk($I);
        $I->assertEquals($this->testUser->getUser()->id, $response['params']['user_id']);
    }

    public function testRefreshToken(\ApiTester $I)
    {
        $request = $this->data->refreshToken();
        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals($request['token'], $response['token']);
    }

    public function testNewToken(\ApiTester $I)
    {
        $request = $this->data->newToken();
        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);

        $I->assertNotEquals($request['token'], $response['token']);
    }

    public function testGetBalance(\ApiTester $I)
    {
        $request = $this->data->getBalance();
        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertNotNull($response['params']['balance']);
    }

    public function testBet(\ApiTester $I)
    {
        $balanceBefore = $this->testUser->getBalanceInCents();
        $request = $this->data->bet();

        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals($balanceBefore - $this->data->getAmount(), $response['params']['balance_after']);

        $this->isRecord($I, $request, 'bet');

        return $request;
    }

    public function testWin(\ApiTester $I)
    {
        $bet = $this->testBet($I);
        $balanceBefore = $this->testUser->getBalanceInCents();
        $request = $this->data->win($bet['params']['bet_id']);
        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals($balanceBefore + $this->data->getAmount(), $response['params']['balance_after']);

        $this->isRecord($I, $request, 'win');

        return $request;
    }

    public function testDuplicateBet(\ApiTester $I)
    {
        $bet = $this->testBet($I);
        $balanceBefore = $this->testUser->getBalanceInCents();
        $request = $this->data->bet(null, $bet['params']['transaction_id']);

        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals(1, $response['params']['already_processed']);
        $I->assertEquals($balanceBefore, $response['params']['balance_after']);
    }

    public function testDuplicateWin(\ApiTester $I)
    {
        $win = $this->testWin($I);

        $balanceBefore = $this->testUser->getBalanceInCents();
        $request = $this->data->win($win['params']['bet_id'], $win['params']['transaction_id']);

        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals(1, $response['params']['already_processed']);
        $I->assertEquals($balanceBefore, $response['params']['balance_after']);
    }

    public function testNoBet(\ApiTester $I)
    {
        $balanceBefore = $this->testUser->getBalanceInCents();
        $request = $this->data->win();
        $I->sendPOST('/bg', $request);
        $this->getResponseFail($I, TransactionHelper::BAD_OPERATION_ORDER);
        $I->assertEquals($balanceBefore, $this->testUser->getBalanceInCents());

        $this->noRecord($I, $request, 'win');
    }

    public function testMultiWin(\ApiTester $I)
    {
        $win = $this->testWin($I);
        $request = $this->data->win($win['params']['bet_id']);
        $balanceBefore = $this->testUser->getBalanceInCents();
        $I->sendPOST('/bg', $request);
        $response = $this->getResponseOk($I);
        $I->assertEquals(1, $response['params']['already_processed']);
        $I->assertEquals($balanceBefore, $this->testUser->getBalanceInCents());

        $this->noRecord($I, $request, 'win');
    }

    public function testExBet(\ApiTester $I)
    {
        $this->data->setAmount(1000000000000000);
        $request = $this->data->bet();
        $balanceBefore = $this->testUser->getBalanceInCents();

        $I->sendPOST('/bg', $request);
        $this->getResponseFail($I, StatusCode::INSUFFICIENT_FUNDS);
        $I->assertEquals($balanceBefore, $this->testUser->getBalanceInCents());
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroBet(\ApiTester $I)
    {
        $this->data->setAmount(0);

        $request = $this->data->bet();
        $I->sendPOST('/bg', $request);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroWin(\ApiTester $I)
    {
        $bet = $this->testBet($I);
        $this->data->setAmount(0);

        $request = $this->data->win($bet['params']['bet_id']);
        $I->sendPOST('/bg', $request);
        $this->getResponseOk($I);

        $this->isRecord($I, $request, 'win');
    }

    /*** test validation ***/

    public function testWrongAmountBet(\ApiTester $I)
    {
        $this->data->setAmount(-100);
        $request = $this->data->bet();

        $I->sendPOST('/bg', $request);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testWrongSignature(\ApiTester $I)
    {
        $data = $this->data->bet();
        $data['signature'] = '123';
        $I->sendPOST('/bg', $data);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
    }

    public function testWrongTime(\ApiTester $I)
    {
        $data = $this->data->wrongTime('get_balance');
        $I->sendPOST('/bg', $data);
        $this->getResponseFail($I, StatusCode::TIME);
    }

    public function testWrongParams(\ApiTester $I)
    {
        $data = $this->data->bet();
        unset($data['params']['amount']);
        $I->sendPOST('/bg', $data);
        $this->getResponseFail($I, StatusCode::SIGNATURE);
    }

    private function getResponseOk(\ApiTester $I)
    {
        $data = $this->responseToArray($I);
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
        return json_decode(json_encode((array)simplexml_load_string($I->grabResponse())), 1);
    }
}