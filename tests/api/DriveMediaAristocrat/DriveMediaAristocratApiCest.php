<?php

class DriveMediaAristocratApiCest
{
    private $options;
    private $space;

    public function _before() {
        $this->options = config('integrations.DriveMediaAristocrat');
        $this->space = "1810";
    }

    public function testMethodBalance(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'space' => $this->space,
            'login' => "{$testUser->id}--1--1--127-0-0-1",
            'cmd'   => 'getBalance',
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
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
            'bet'       => '0.05',
            'winLose'   => '-0.05',
            'tradeId'   => (string)rand(1111111111111,9999999999999).'_'.rand(111111111,999999999),
            'betInfo'   => 'Bet',
            'gameId'    => '123',
            'matrix'    => 'EAGLE,DINGO,BOAR,BOAR,BOAR,;TEN,JACK,KING,QUEEN,TEN,;DINGO,BOAR,DINGO,DINGO,SCATTER,;',
            'WinLines'  => 0,
            'date'      => time()
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'balance'   => money_format('%i', $testUser->getBalance() - 0.05),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}