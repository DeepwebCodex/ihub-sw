<?php

use iHubGrid\Accounting\Users\IntegrationUser;

class DriveMediaAmaticBorderlineApiCest
{
    private $options;
    private $space;

    public function _before() {
        $this->options = config('integrations.DriveMediaAmatic');
        $this->space = '1811';
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'space'     => $this->space,
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'cmd'       => 'writeBet',
            'bet'       => '0.0',
            'winLose'   => '0.1',
            'tradeId'   => (string)microtime().rand(0,9),
            'betInfo'   => 'bet',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => '[6,5,3,6,1,8,7,5,4]',
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'space'     => $this->space,
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'cmd'       => 'writeBet',
            'bet'       => '0.1',
            'winLose'   => '0.1',
            'tradeId'   => (string)microtime(),
            'betInfo'   => 'bet',
            'gameId'    => '183',
            'matrix'    => '[6,5,3,6,1,8,7,5,4]',
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', ($testUser->getBalance() - 0.1 + 0.1)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWrongSign(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'space' => $this->space,
            'login' => "{$testUser->id}--1--1--127-0-0-1",
            'cmd'   => 'getBalance',
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5(http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

}