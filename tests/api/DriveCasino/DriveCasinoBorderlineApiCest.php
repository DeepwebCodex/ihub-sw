<?php

class DriveCasinoBorderlineApiCest
{
    private $options;
    private $space;

    public function _before() {
        $this->options = config('integrations.drivecasino');
        $this->space = "1812";
    }

    public function testMethodZeroWin(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => (string)$testUser->id,
            'bet'       => 0,
            'winLose'   => 0,
            'tradeId'   => md5(time()),
            'betInfo'   => 'win',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', ($testUser->getBalance())),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $tradeId = md5(time());

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => (string)$testUser->id,
            'bet'       => 1,
            'winLose'   => -1,
            'tradeId'   => $tradeId,
            'betInfo'   => 'bet',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', ($testUser->getBalance() - 1)),
            'status'    => 'success',
            'error'     => ''
        ]);

        //WIN
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => (string)$testUser->id,
            'bet'       => 0,
            'winLose'   => 5,
            'tradeId'   => $tradeId,
            'betInfo'   => 'win',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', ($testUser->getBalance() - 1 + 5)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => (string)$testUser->id,
            'bet'       => 0,
            'winLose'   => 2,
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'win',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

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
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => (string)$testUser->id,
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5(http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

}