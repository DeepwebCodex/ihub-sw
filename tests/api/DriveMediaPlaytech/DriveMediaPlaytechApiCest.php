<?php

class DriveMediaPlaytechApiCest
{
    private $options;

    public function _before() {
        $this->options = config('integrations.DriveMediaPlaytech');
    }

    public function testMethodBalance(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'cmd'   => 'getBalance',
            'space' => '1805',
            'login' => (string)$testUser->id,
        ];

        $request = array_merge($request, ['sign'  => strtoupper(md5($this->options['1805']['key'].http_build_query($request)))]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => (string)$testUser->id,
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
            'space'     => '1805',
            'login'     => (string)$testUser->id,
            'bet'       => '0.10',
            'winLose'   => '-0.10',
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
            'login'     => (string)$testUser->id,
            'balance'   => money_format('%i', ($testUser->getBalance() - 0.10)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}