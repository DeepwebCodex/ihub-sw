<?php

use DriveMedia\TestUser;

class DriveMediaIgrosoftBorderlineApiCest
{

    private $key;
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before() {
        $this->key = config('integrations.DriveMediaIgrosoft.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaIgrosoft.spaces.FUN.space');

        $this->testUser = new TestUser();
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->testUser->getUserId(),
            'bet'       => '0.00',
            'winLose'   => '0.30',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'CollectWin',
            'gameId'    => (string)hexdec(substr(md5(microtime()), 0, 5)),
            'matrix'    => '7,8,6,;8,7,2,;2,8,7,;3,8,7,;6,7,8,;',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

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
        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => $this->testUser->getUserId(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5(http_build_query($request)))
        ]);

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