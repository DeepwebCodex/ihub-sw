<?php

class DriveMediaIgrosoftBorderlineApiCest
{

    private $options;
    private $space;

    public function _before() {
        $this->options = config('integrations.DriveMediaIgrosoft');
        $this->space = '1809';
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => "{$testUser->id}--1--1--127-0-0-1",
            'bet'       => '0.00',
            'winLose'   => '0.30',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'CollectWin',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => '7,8,6,;8,7,2,;2,8,7,;3,8,7,;6,7,8,;',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/igrosoft', $request);
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
            'login' => "{$testUser->id}--1--1--127-0-0-1",
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5(http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/igrosoft', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

}