<?php

namespace api\Endorphina;

use ApiTester;
use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Integrations\GameSession\GameSessionService;
use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;
use Codeception\Scenario;
use Endorphina\TestData;
use Testing\AccountManager\Protocol\ProtocolV1;
use Testing\GameSessionsMock;
use function GuzzleHttp\json_decode;

class EndorphinaApiCest
{

    private $data;

    public function __construct()
    {
        $this->data = new TestData(new ProtocolV1());
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

    private function getResponseFail(ApiTester $I, $errorCode, $code = '', $message = '')
    {
        $data = $this->responseToArray($I);
        $I->assertArrayHasKey('code', $data);
        $I->assertArrayHasKey('message', $data);
        if ($message) {
            $I->assertEquals($message, $data['message']);
        }
        $I->assertEquals($code, $data['code']);
        $I->assertNotEmpty($data['message']);
        $I->seeResponseCodeIs($errorCode);
        $I->canSeeResponseIsJson();
        return $data;
    }

    private function isRecord(ApiTester $I, string $transaction_id, string $method, $amount = null)
    {
        $params = [
            'foreign_id' => $transaction_id,
            'transaction_type' => $method,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ];
        if ($amount !== null) {
            $params['amount'] = $amount;
        }
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, $params);
    }

    private function countRecord(ApiTester $I, string $transaction_id, string $method, int $count)
    {
        $I->expect('Can see record of transaction applied');
        $transactions = Transactions::where([
                    'foreign_id' => $transaction_id,
                    'transaction_type' => $method,
                    'status' => TransactionRequest::STATUS_COMPLETED
                ])->count();
        $I->assertEquals($count, $transactions);
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
        $I->assertEquals($this->data->user->getBalanceInCents(), $data['balance']);
    }

    public function testBet(ApiTester $I)
    {
        $packet = $this->data->getPacketBet();
        $I->sendPOST('/endorphina/bet/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() - $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_BET);
        return $packet;
    }

    public function testWin(ApiTester $I)
    {
        $this->testBet($I);
        $packet = $this->data->getPacketWin();
        $I->sendPOST('/endorphina/win/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() + $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_WIN);
    }

    public function testWinAmountZero(ApiTester $I)
    {
        $this->testBet($I);
        $packet = $this->data->getPacketWin(0);
        $I->sendPOST('/endorphina/win/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() + $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_WIN);
    }

    public function testRefund(ApiTester $I)
    {
        $dataBet = $this->testBet($I);
        $packet = $this->data->getPacketRefund($dataBet['id']);
        $I->sendPOST('/endorphina/refund/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() + $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_REFUND);
    }

    public function testBetInsufficientFunds(ApiTester $I)
    {
        $packet = $this->data->getPacketBet(0, 0);
        $I->sendPOST('/endorphina/bet/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() - $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_BET);
        return $packet;
    }

    //Exception situations
    public function testRefundWithoutBet(ApiTester $I)
    {
        $packet = $this->data->getPacketRefundWithoutBet();
        $I->sendPOST('/endorphina/refund/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents();
        $I->assertEquals($balance, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_REFUND);
        return $packet;
    }

    public function testBetAfterRefund(ApiTester $I)
    {
        $refundPacket = $this->testRefundWithoutBet($I);
        $packet = $this->data->getPacketBet($refundPacket['id']);
        $I->sendPOST('/endorphina/bet/', $packet);
        $error = CodeMapping::getByErrorCode(StatusCode::BAD_ORDER);
        $this->getResponseFail($I, 500, StatusCode::EXTERNAl_INTERNAL_ERROR, $error['message']);
    }

    public function testDuplicateOperation(ApiTester $I)
    {
        $packetBet = $this->testBet($I);
        $packet = $this->data->getPacketBet($packetBet['id']);
        $I->sendPOST('/endorphina/bet/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($this->data->user->getBalanceInCents(), $data['balance']);
        $this->countRecord($I, $packetBet['id'], TransactionRequest::TRANS_BET, 1);
    }

    public function testContinueOperationCompleted(ApiTester $I)
    {
        $packetBet = $this->testBet($I);
        $transaction = Transactions::where([
                    'foreign_id' => $packetBet['id'],
                    'transaction_type' => TransactionRequest::TRANS_BET,
                    'status' => TransactionRequest::STATUS_COMPLETED
                ])->first();
        $transaction->status = TransactionRequest::STATUS_PENDING;
        $transaction->save();
        $packet = $this->data->getPacketBet($packetBet['id']);
        $I->sendPOST('/endorphina/bet/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() - $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $this->countRecord($I, $packetBet['id'], TransactionRequest::TRANS_BET, 1);
    }

    public function testContinueOperationPending(ApiTester $I)
    {
        $packetBet = $this->testBet($I);
        $transaction = Transactions::where([
                    'foreign_id' => $packetBet['id'],
                    'transaction_type' => TransactionRequest::TRANS_BET,
                    'status' => TransactionRequest::STATUS_COMPLETED
                ])->first();
        $transaction->status = TransactionRequest::STATUS_PENDING;
        $transaction->save();
        $packet = $this->data->getPacketBet($packetBet['id']);
        $I->sendPOST('/endorphina/bet/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $balance = $this->data->user->getBalanceInCents() - $packet['amount'];
        $I->assertEquals($balance, $data['balance']);
        $this->countRecord($I, $packetBet['id'], TransactionRequest::TRANS_BET, 1);
    }

}
