<?php

namespace api\Fundist;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Transactions\Strategies\Fundist\ProcessFundist;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Codeception\Scenario;
use \Fundist\TestData;
use \Fundist\TestUser;
use Symfony\Component\HttpFoundation\Response;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\GameSessionsMock;
use Testing\Params;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class LiveDealerApiCest
{
    /** @var  TestData */
    private $data;
    private $action;

    /** @var TestUser */
    private $testUser;

    const OFFLINE = [
        'test ping',
        'test fail auth',
    ];

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData('liveDealer');
        $this->action = '/livedealer';
    }

    public function _before(\ApiTester $I, Scenario $s)
    {
        $I->mockAccountManager($I, config('integrations.liveDealer.service_id'));
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
        $I->sendPOST($this->action, json_encode($this->data->getBalance()));
        $data = $this->getResponseOk($I);
        $I->assertNotNull($data['balance']);
    }

    public function testBet(\ApiTester $I)
    {
        $balanceBefore = $this->testUser->getBalance();

        $request = $this->data->bet();

        $I->sendPOST($this->action, json_encode($request));
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
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I);
    }


    public function testExBet(\ApiTester $I)
    {
        $this->data->setAmount($this->data->bigAmount);
        $request = $this->data->bet();
        $balanceBefore = $this->testUser->getBalance();

        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::INSUFFICIENT_FUNDS);
        $I->assertEquals($balanceBefore, $this->testUser->getBalance());
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testFailAuth(\ApiTester $I)
    {
        $I->sendPOST($this->action, json_encode($this->data->authFailed()));
        $this->getResponseFail($I, StatusCode::TOKEN);
    }

    public function testZeroBet(\ApiTester $I)
    {
        $this->data->setAmount(0.00);

        $request = $this->data->bet();
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I);
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroWin(\ApiTester $I)
    {
        $bet = $this->testBet($I);
        $this->data->setAmount(0.00);

        $request = $this->data->win($bet['i_gameid']);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseOk($I, true);
        $this->data->resetAmount();

        $this->isRecord($I, $request, 'win');
    }

    public function testDuplicateBet(\ApiTester $I)
    {
        $betData = $this->data->bet();
        $I->sendPOST($this->action, json_encode($betData));

        $balanceBefore = $this->testUser->getBalance();
        $request = $this->data->bet(null, $betData['tid']);

        $I->sendPOST($this->action, json_encode($request));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balanceBefore, $response['balance']);
    }



    public function testWin(\ApiTester $I)
    {
        $bet = $this->data->bet();
        $I->sendPOST($this->action, json_encode($bet));

        $balanceBefore = $this->testUser->getBalance();
        $request = $this->data->win($bet['i_gameid']);
        $I->sendPOST($this->action, json_encode($request));
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

        $I->sendPOST($this->action, json_encode($request));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balanceBefore, $response['balance']);
    }

    public function testRound(\ApiTester $I)
    {
        $request = $this->data->roundInfo();
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseOk($I);
    }

    public function testNoBet(\ApiTester $I)
    {
        $balanceBefore = $this->testUser->getBalance();
        $game_number = $this->getUniqueNumber();
        $request = $this->data->win($game_number);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::BAD_OPERATION_ORDER);
        $I->assertEquals($balanceBefore, $this->testUser->getBalance());

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
        $request = $this->data->bet();
        $I->sendPOST($this->action, json_encode($request));

        $this->transMismatch($I, $request, 'userid', time() . '_' . Params::CURRENCY);
        $this->transMismatch($I, $request, 'currency', 'QQ');
        $this->transMismatch($I, $request, 'amount', $request['amount'] + 1);
    }

    /** fail in runtime */
    public function testFailPending(\ApiTester $I)
    {
        $mock = $this->mock(ProcessFundist::class);
        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);
        $mock->shouldReceive('runPending')->once()->withNoArgs()->andThrow(new GenericApiHttpException(500, $error['message'], [], null, [], $error['code']));
        $request = $this->data->bet();
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::UNKNOWN, Response::HTTP_REQUEST_TIMEOUT);
        $this->noRecord($I, $request, 'bet');
    }

    public function testFailDb(\ApiTester $I)
    {
        $mock = $this->mock(ProcessFundist::class);
        $mock->shouldReceive('writeTransaction')->once()->withNoArgs()->andThrow(new \RuntimeException("", 500));
        $request = $this->data->bet();
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