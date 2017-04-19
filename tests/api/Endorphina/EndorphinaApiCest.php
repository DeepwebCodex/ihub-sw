<?php

namespace api\BetGames;

use ApiTester;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Integrations\GameSession\GameSessionService;
use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;
use Codeception\Scenario;
use Endorphina\TestData;
use Helper\TestUser;
use Testing\AccountManager\Protocol\ProtocolV1;
use Testing\GameSessionsMock;
use function GuzzleHttp\json_decode;

class EndorphinaApiCest
{

    private $data;
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData($this->testUser);
        $this->protocol = new ProtocolV1();
    }

    public function _before(ApiTester $I, Scenario $s)
    {
        $this->data->setI($I);
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    private function getResponseOk(ApiTester $I)
    {
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $data = $this->responseToArray($I);
        return $data;
    }

    private function getResponseFail(ApiTester $I, $errorCode, $code = '')
    {
        $data = $this->responseToArray($I);
        $I->assertArrayHasKey('code', $data);
        $I->assertArrayHasKey('message', $data);
        $I->assertEquals($code, $data['code']);
        $I->assertNotEmpty($data['message']);
        $I->seeResponseCodeIs($errorCode);
        $I->canSeeResponseIsJson();
        return $data;
    }

    private function isRecord(ApiTester $I, string $transaction_id, string $method)
    {
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $transaction_id,
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function noRecord(ApiTester $I, $request, $method)
    {
        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(Transactions::class, [
            'foreign_id' => $request['params']['transaction_id'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function responseToArray(ApiTester $I)
    {
        $data = $I->grabResponse();
        return json_decode($data, true);
    }

    public function testMethodNotFound(ApiTester $I)
    {
        $I->sendPOST('/endorphina/unknownmethod/', []);
        $this->getResponseFail($I, 500, StatusCode::EXTERNAl_INTERNAL_ERROR);
    }

    public function testSession(ApiTester $I)
    {
        $packet = $this->data->getPacketSession();
        $I->sendGET('/endorphina/session/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('player', $data);
        $I->assertArrayHasKey('currency', $data);
        $I->assertArrayHasKey('game', $data);
        $I->assertEquals($this->data->currency, $data['currency']);
        $I->assertEquals($this->data->userId, $data['player']);
    }

    public function testBalance(ApiTester $I)
    {
        $packet = $this->data->getPacketBalance();
        $I->sendGET('/endorphina/balance/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertEquals($this->testUser->getBalanceInCents(), $data['balance']);
    }

    public function testBet(ApiTester $I)
    {
        $packet = $this->data->getPacketBet();
        $I->sendPOST('/endorphina/bet/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->testUser->getBalanceInCents() - $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], 'bet');
    }

    public function testWin(ApiTester $I)
    {
        $packet = $this->data->getPacketWin();
        $I->sendPOST('/endorphina/win/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->testUser->getBalanceInCents() + $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], 'bet');
    }

}
