<?php

class DriveCasinoApiCest
{

    private $options;
    private $space;

    public function _before() {
        $this->options = config('integrations.drivecasino');
        $this->space = "1812";
    }

    public function testMethodBalance(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => "{$testUser->id}--1--1--127-0-0-1",
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', $testUser->getBalance()),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'bet'       => 1,
            'winLose'   => -1,
            'tradeId'   => md5(microtime()),
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
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', ($testUser->getBalance() - 1)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

}