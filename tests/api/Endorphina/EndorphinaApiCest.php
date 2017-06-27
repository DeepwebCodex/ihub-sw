<?php
namespace api\Endorphina;

use ApiTester;
use App\Components\Integrations\Endorphina\StatusCode;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Endorphina\TestData;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use Testing\GameSessionsMock;
use function GuzzleHttp\json_decode;

class EndorphinaApiCest
{

    /** @var TestData  */
    private $data;

    /** @var Params  */
    private $params;

    public function __construct()
    {
        $this->params = new Params('endorphina');
        $this->data = new TestData($this->params);
    }

    public function _before(ApiTester $I)
    {
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function testMethodNotFound(ApiTester $I)
    {
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/unknownmethod/', []);
        $this->getResponseFail($I, 500, StatusCode::EXTERNAl_INTERNAL_ERROR);
    }

    public function testSession(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $packet = $this->data->getPacketSession();
        $I->sendGET('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/session/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('player', $data);
        $I->assertArrayHasKey('currency', $data);
        $I->assertArrayHasKey('game', $data);
        $I->assertEquals($this->params->currency, $data['currency']);
        $I->assertEquals($this->params->userId, $data['player']);
    }

    public function testBalance(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $packet = $this->data->getPacketBalance();
        $I->sendGET('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/balance/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertEquals($this->params->getBalanceInCents(), $data['balance']);
    }

    public function testBet(ApiTester $I)
    {
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = random_int(100000, 9900000);
        $bet = 1300;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I);

        $packet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents - $bet, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_BET);
        return $packet;
    }

    public function testWin(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 1000;
        $win = 2000;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->win($objectId, $win / 100, $balance - $bet / 100 + $win / 100)
            ->mock($I);

        $packet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        $packet = $this->data->getPacketWin($win);

        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/win/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents - $bet + $win, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_WIN);
    }

    public function testWinAmountZero(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 1000;

        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo(($balanceInCents - $bet) / 100)
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I);

        // bet
        $packetBet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packetBet);

        // win
        $packet = $this->data->getPacketWin(0);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/win/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents - $bet, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        //TODO: it some reason, that DB hasn't record. Need explore and fix. When you push zero transaction foreign-id will be generate random
        //$this->isRecord($I, $packet['id'], TransactionRequest::TRANS_WIN);
    }

    public function testRefund(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 1000;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->win($objectId, $bet / 100, $balance)
            ->mock($I);

        // bet
        $packet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        // refund
        $packet = $this->data->getPacketRefund($packet['id'], $bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/refund/', $packet);

        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_REFUND);
    }

    //Exception situations
    public function testRefundWithoutBet(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 1000;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId($objectId)
            ->win($objectId, $bet / 100, $balance)
            ->mock($I);

        $packet = $this->data->getPacketRefundWithoutBet();
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/refund/', $packet);
        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents, $data['balance']);
        $I->assertGreaterThan(1, $data['transactionId']);
        $this->isRecord($I, $packet['id'], TransactionRequest::TRANS_REFUND);
        return $packet;
    }

    public function testBetAfterRefund(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 1000;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->win($objectId, $bet / 100, $balance)
            ->mock($I);

        // refund
        $refundPacket = $this->data->getPacketRefundWithoutBet();
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/refund/', $refundPacket);

        // bet
        $packet = $this->data->getPacketBet($bet, $refundPacket['id']);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
    }

    public function testDuplicateOperation(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 1000;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo(($balanceInCents - $bet) / 100)
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I);

        // bet
        $packetBet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packetBet);

        // bet
        $packet = $this->data->getPacketBet($bet, $packetBet['id']);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents - $bet, $data['balance']);
        $this->countRecord($I, $packetBet['id'], TransactionRequest::TRANS_BET, 1);
    }

    public function testContinueOperationCompleted(ApiTester $I)
    {
        $objectId = random_int(100000, 9900000);
        $bet = 100;
        $balance = $this->params->getBalance();
        $balanceInCents = $this->params->getBalanceInCents();

        (new AccountManagerMock($this->params))
            ->userInfo(($balanceInCents - $bet) / 100)
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I, false);

        // bet
        $packetBet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packetBet);

        // change transaction status to 'pending'
        $transaction = Transactions::where([
                'foreign_id' => $packetBet['id'],
                'transaction_type' => TransactionRequest::TRANS_BET,
                'status' => TransactionRequest::STATUS_COMPLETED
            ])->first();
        $transaction->status = TransactionRequest::STATUS_PENDING;
        $transaction->save();

        (new AccountManagerMock($this->params))
            ->userInfo(($balanceInCents - $bet) / 100)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I);

        // bet
        $packet = $this->data->getPacketBet($bet, $packetBet['id']);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents - $bet, $data['balance']);
        $this->countRecord($I, $packetBet['id'], TransactionRequest::TRANS_BET, 1);
    }

    public function testContinueOperationPending(ApiTester $I)
    {
        $balanceInCents = $this->params->getBalanceInCents();
        $objectId = random_int(100000, 9900000);
        $bet = 1300;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId($objectId)
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I, false);

        // bet
        $packetBet = $this->data->getPacketBet($bet);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packetBet);

        $transaction = Transactions::where([
                'foreign_id' => $packetBet['id'],
                'transaction_type' => TransactionRequest::TRANS_BET,
                'status' => TransactionRequest::STATUS_COMPLETED
            ])->first();
        $transaction->status = TransactionRequest::STATUS_PENDING;
        $transaction->save();

        // bet
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet / 100, $balance - $bet / 100)
            ->mock($I);

        $packet = $this->data->getPacketBet($bet, $packetBet['id']);
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);

        $data = $this->getResponseOk($I);
        $I->assertArrayHasKey('balance', $data);
        $I->assertArrayHasKey('transactionId', $data);
        $I->assertEquals($balanceInCents - $bet, $data['balance']);
        $this->countRecord($I, $packetBet['id'], TransactionRequest::TRANS_BET, 1);
    }

    public function testWrongSign(ApiTester $I)
    {
        $packet = $this->data->getPacketBet();
        $packet['sign'] = md5('wrong');
        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);
        $this->getResponseFail($I, 401, StatusCode::EXTERNAl_ACCESS_DENIED);
    }

    public function testWrongPacket(ApiTester $I)
    {
        $packet = $this->data->getWrongPacketBet();

        $I->sendPOST('/endorphina/' . $this->params->partnerId . '/' . $this->params->cashdeskId . '/bet/', $packet);
        $this->getResponseFail($I, 500, StatusCode::EXTERNAl_INTERNAL_ERROR);
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
}
