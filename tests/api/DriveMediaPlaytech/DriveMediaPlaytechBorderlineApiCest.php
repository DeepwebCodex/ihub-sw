<?php

use iHubGrid\Accounting\Users\IntegrationUser;

class DriveMediaPlaytechBorderlineApiCest
{
    private $options;

    public function _before() {
        $this->options = config('integrations.DriveMediaPlaytech');
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => '1805',
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'bet'       => '2.00',
            'winLose'   => '5.8',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options['1805']['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', ($testUser->getBalance() - 2.0 + 7.8)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin2(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => '1805',
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'bet'       => '240.0',
            'winLose'   => '-180.0',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options['1805']['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', ($testUser->getBalance() - 240.0 + 60.0)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin3(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => '1805',
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'bet'       => '360.0',
            'winLose'   => '90.0',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options['1805']['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', ($testUser->getBalance() - 360.0 + 450.0)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => '1805',
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'bet'       => '0.00',
            'winLose'   => '0.10',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'spin',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options['1805']['key'].http_build_query($request)))]);

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
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'   => 'getBalance',
            'space' => '1805',
            'login' => "{$testUser->id}--1--1--127-0-0-1",
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5(http_build_query($request)))]);

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
        $request = [
            'cmd'   => 'getBalance',
            'space' => '1805',
            'login' => "348578934578934570702728--1--1--127-0-0-1",
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options['1805']['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'user_not_found'
        ]);
    }

}