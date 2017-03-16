<?php

class DriveMediaIgrosoftApiCest
{
    private $options;
    private $space;

    public function _before() {
        $this->options = config('integrations.DriveMediaIgrosoft');
        $this->space = "1809";
    }

    public function testMethodBalance(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => (string)$testUser->id,
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/igrosoft', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', $testUser->getBalance()),
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
            'bet'       => '0.10',
            'winLose'   => '-0.10',
            'tradeId'   => $tradeId,
            'betInfo'   => 'SpinNormal',
            'gameId'    => '183',
            'matrix'    => '7,8,6,;8,7,2,;2,8,7,;3,8,7,;6,7,8,;',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/igrosoft', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', ($testUser->getBalance() - 0.10)),
            'status'    => 'success',
            'error'     => ''
        ]);

        //WIN
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => (string)$testUser->id,
            'bet'       => '0.00',
            'winLose'   => '0.50',
            'tradeId'   => $tradeId,
            'betInfo'   => 'CollectWin',
            'gameId'    => '183',
            'matrix'    => '7,8,6,;8,7,2,;2,8,7,;3,8,7,;6,7,8,;',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options[$this->space]['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/igrosoft', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', ($testUser->getBalance() - 0.10 + 0.50)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}