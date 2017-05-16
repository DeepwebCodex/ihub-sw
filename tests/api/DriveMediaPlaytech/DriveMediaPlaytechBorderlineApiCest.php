<?php

use App\Models\DriveMediaPlaytechProdObjectIdMap;
use Testing\DriveMedia\AccountManagerMock;
use Testing\DriveMedia\Params;

class DriveMediaPlaytechBorderlineApiCest
{
    private $key;
    private $space;

    /** @var Params  */
    private $params;

    public function _before() {
        $this->key = config('integrations.DriveMediaPlaytech.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaPlaytech.spaces.FUN.id');

        $this->params = new Params('DriveMediaPlaytech');
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $tradeId = md5(microtime());
        $objectId = DriveMediaPlaytechProdObjectIdMap::getObjectId($tradeId);
        $bet = 2.00;
        $winLose = 5.8;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->bet($objectId, $bet, $balance - $bet)
            ->win($objectId, $bet + $winLose, $balance - $bet)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $balance + $winLose),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin2(ApiTester $I)
    {
        $tradeId = md5(microtime());
        $objectId = DriveMediaPlaytechProdObjectIdMap::getObjectId($tradeId);
        $bet = 240.0;
        $winLose = -180.0;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->bet($objectId, $bet)
            ->win($objectId, $bet + $winLose, $balance - $bet)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $balance + $winLose),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin3(ApiTester $I)
    {
        $tradeId = md5(microtime());
        $objectId = DriveMediaPlaytechProdObjectIdMap::getObjectId($tradeId);
        $bet = 360.0;
        $winLose = 90.0;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->bet($objectId, $bet)
            ->win($objectId, $bet + $winLose, $balance - $bet)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->params->login,
            'balance'   => money_format('%i', ($balance + $winLose)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => '0.00',
            'winLose'   => '0.10',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'spin',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
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
            'login' => $this->params->login,
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5(http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

    public function testMethodUserNotFound(ApiTester $I)
    {
        (new AccountManagerMock($this->params))->userNotFound(3485789345789345)->mock($I);
        $request = [
            'cmd'   => 'getBalance',
            'space' => '1805',
            'login' => "3485789345789345--1--1--127-0-0-1",
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        //TODO fix response code
//        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'user_not_found'
        ]);
    }

    public function testMethodSpaceNotFound(ApiTester $I)
    {
        $request = [
            'cmd'   => 'getBalance',
            'space' => '1',
            'login' => $this->params->login,
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }

}