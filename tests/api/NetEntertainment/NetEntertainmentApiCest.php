<?php

namespace api\NetEntertainment;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Integrations\NetEntertainment\NetEntertainmentHelper;
use App\Components\Transactions\Strategies\NetEntertainment\ProcessNetEntertainment;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Codeception\Scenario;
use \NetEntertainment\TestData;
use \Fundist\TestUser;
use Symfony\Component\HttpFoundation\Response;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use Testing\GameSessionsMock;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class NetEntertainmentApiCest
{
    /** @var  TestData */
    private $data;
    private $action;

    /** @var TestUser */
    private $testUser;

    /** @var Params  */
    private $params;

    const OFFLINE = [
        'test ping',
        'test fail auth',
    ];

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData('netEntertainment');
        $this->action = '/netent';
        $this->objectIdKey = 'i_gameid';
        $this->params = new Params('netEntertainment');
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
        $I->assertTrue(is_array(explode('.', $data['balance'])));
        $I->assertTrue(count(explode('.', $data['balance'])) == 2);
    }

    public function testBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();

        $bet = 1;
        $request = $this->data->bet($bet);
        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$request['i_gameid'], $request['i_actionid']);
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $bet, $balance - $bet)
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
        $amount = 10000000235235;
        $request = $this->data->bet($amount);

        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$request['i_gameid'], $request['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->betExceeded($object_id, $amount)
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
        $betAmount = 0.00;
        $balance = $this->params->getBalance();
        $request = $this->data->bet($betAmount);

        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$request['i_gameid'], $request['i_actionid']);
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $betAmount, $balance - $betAmount)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I);
        $this->data->resetAmount();

        $this->noRecord($I, $request, 'bet');
    }

    public function testZeroWin(\ApiTester $I)
    {
        $balance = $this->params->getBalance();

        $bet = 1;
        $betRequest = $this->data->bet($bet);
        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$betRequest['i_gameid'], $betRequest['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->bet($object_id, $bet, $balance - $bet)
            ->win($object_id, $bet, $balance - $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($betRequest));

        //win
        $request = $this->data->win(0.00, $betRequest[$this->objectIdKey]);

        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseOk($I, true);

        $this->isRecord($I, $request, 'win');
    }

    public function testDuplicateBet(\ApiTester $I)
    {
        $balance = $this->params->getBalance();

        $bet = 123;
        $game_id1 = $this->data->getUniqueNumber();
        $game_id2 = $this->data->getUniqueNumber();

        $betData = $this->data->bet($bet, $game_id1);
        $betData2 = $this->data->bet($bet, $game_id2, $betData['tid']);

        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$betData['i_gameid'], $betData['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet)
            ->bet($object_id, $bet, $balance - $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($betData));
        $this->getResponseOk($I, true);

        $I->sendPOST($this->action, json_encode($betData2));
        $this->getResponseOk($I, true);

        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balance - $bet, $response['balance']);
    }

    public function testWin(\ApiTester $I)
    {
        $balance = $this->params->getBalance();

        $bet = 1;
        $win = 3;
        $betRequest = $this->data->bet($bet);
        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$betRequest['i_gameid'], $betRequest['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $bet, $balance - $bet)
            ->win($object_id, $win, $balance - $bet + $win)
            ->mock($I);

        // bet
        $I->sendPOST($this->action, json_encode($betRequest));

        // win
        $request = $this->data->win($win, $betRequest[$this->objectIdKey]);
        $I->sendPOST($this->action, json_encode($request));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balance - $bet + $win, $response['balance']);

        $this->isRecord($I, $request, 'win');

        return $request;
    }

    public function testDuplicateWin(\ApiTester $I)
    {
        $balance = $this->params->getBalance();

        $bet = 1;
        $win = 3;
        $betRequest = $this->data->bet($bet);
        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$betRequest['i_gameid'], $betRequest['i_actionid']);

        // bet
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $bet, $balance - $bet)
            ->mock($I, false);

        $I->sendPOST($this->action, json_encode($betRequest));

        // win
        (new AccountManagerMock($this->params))
            ->userInfo($balance - $bet + $win)
            ->win($object_id, $win, $balance - $bet + $win)
            ->mock($I);

        $winRequest = $this->data->win($win, $betRequest[$this->objectIdKey]);
        $I->sendPOST($this->action, json_encode($winRequest));
        $response = $this->getResponseOk($I, true);
        $I->assertEquals($balance - $bet + $win, $response['balance']);

        // win duplicated
        $winRequest2 = $this->data->win($win, $winRequest[$this->objectIdKey], $winRequest['tid']);
        $I->sendPOST($this->action, json_encode($winRequest2));
        $response = $this->getResponseOk($I, true);

        $I->assertEquals($balance - $bet + $win, $response['balance']);
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

    public function testMismatch(\ApiTester $I)
    {
        $bet = 1;
        $request = $this->data->bet($bet);
        $object_id = NetEntertainmentHelper::getObjectIdMap((int)$request['i_gameid'], $request['i_actionid']);

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $bet)
            ->mock($I);

        $I->sendPOST($this->action, json_encode($request));

        $this->transMismatch($I, $request, 'userid', '1' . $request['userid']); // Another user in credit
        $this->transMismatch($I, $request, 'currency', 'QQ'); // Another currency in crefit
        $this->transMismatch($I, $request, 'amount', $request['amount'] + 1); // Another amount in credit
    }

    private function transMismatch(\ApiTester $I, $request, $attr, $value)
    {
        $request[$attr] = $value;
        $request = $this->data->renewHmac($request);
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::TRANSACTION_MISMATCH);
    }

    /** fail in runtime */
    public function testFailPending(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $mock = $this->mock(ProcessNetEntertainment::class);
        $error = CodeMapping::getByErrorCode(StatusCode::UNKNOWN);
        $mock->shouldReceive('runPending')->once()->withNoArgs()->andThrow(new GenericApiHttpException(500, $error['message'], [], null, [], $error['code']));
        $request = $this->data->bet();
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::UNKNOWN, Response::HTTP_REQUEST_TIMEOUT);
        $this->noRecord($I, $request, 'bet');
    }

    public function testFailDb(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $mock = $this->mock(ProcessNetEntertainment::class);
        $mock->shouldReceive('writeTransaction')->once()->withNoArgs()->andThrow(new \RuntimeException("", 500));
        $request = $this->data->bet();
        $I->sendPOST($this->action, json_encode($request));
        $this->getResponseFail($I, StatusCode::UNKNOWN, Response::HTTP_REQUEST_TIMEOUT);
        $this->noRecord($I, $request, 'bet');
    }

    protected function getUniqueNumber()
    {
        return time() + random_int(1, 10000);
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
        $I->assertArrayNotHasKey('error', $data);
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