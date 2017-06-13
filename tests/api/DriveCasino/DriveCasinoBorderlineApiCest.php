<?php

use App\Models\DriveCasinoProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveCasinoBorderlineApiCest
{
    private $key;
    private $space;

    /** @var Params  */
    private $params;

    /** @var Helper  */
    private $helper;

    public function _before() {
        $this->space = config('integrations.drivecasino.spaces.FUN.id');
        $this->key = config('integrations.drivecasino.spaces.FUN.key');

        $this->params = new Params('drivecasino');
        $this->helper = new Helper($this->params);
    }

    public function testMethodZeroWin(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'bet'       => 0,
            'winLose'   => 0,
            'tradeId'   => md5(time()),
            'betInfo'   => 'win',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', ($this->params->getBalance())),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $tradeId = md5(time());
        $objectId = DriveCasinoProdObjectIdMap::getObjectId($tradeId);
        $bet1 = 1;
        $winLose1 = -1;
        $bet2 = 0;
        $winLose2 = 5;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet1, $balance - $bet1)->win($objectId, $winLose2, $balance + $winLose2)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'bet'       => $bet1,
            'winLose'   => $winLose1,
            'tradeId'   => $tradeId,
            'betInfo'   => 'bet',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', ($balance - $bet1)),
            'status'    => 'success',
            'error'     => ''
        ]);

        //WIN
        $balance = $this->params->getBalance();
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'bet'       => $bet2,
            'winLose'   => $winLose2,
            'tradeId'   => $tradeId,
            'betInfo'   => 'win',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', ($balance + $winLose2)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $tradeId = md5(microtime());
        $objectId = DriveCasinoProdObjectIdMap::getObjectId($tradeId);
        $bet = 0;
        $winLose = 2;
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
            'betInfo'   => 'win',
            'gameId'    => (string)$objectId,
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }

    public function testMethodWrongSign(ApiTester $I)
    {
        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => $this->helper->getLogin(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5(http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

    public function testMethodSpaceNotFound(ApiTester $I)
    {
        $request = [
            'cmd'   => 'getBalance',
            'space' => '1',
            'login' => $this->helper->getLogin(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }

}