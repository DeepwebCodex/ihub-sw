<?php

use iHubGrid\Accounting\Users\IntegrationUser;

use DriveMedia\TestUser;

class DriveMediaAristocratBorderlineApiCest
{
    private $key;
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before() {
        $this->key = config('integrations.DriveMediaAristocrat.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAristocrat.spaces.FUN.id');

        $this->testUser = new TestUser();
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $request = [
            'cmd' => 'writeBet',
            'space' => $this->space,
            'login' => $this->testUser->getUserId(),
            'bet' => '0.05',
            'winLose' => '-0.03',
            'tradeId' => (string)rand(1111111111111,9999999999999).'_'.rand(111111111,999999999),
            'betInfo' => 'Bet',
            'gameId' => '125',
            'matrix' => 'EAGLE,DINGO,BOAR,BOAR,BOAR,;TEN,JACK,KING,QUEEN,TEN,;DINGO,BOAR,DINGO,DINGO,SCATTER,;',
            'WinLines' => 0,
            'date' => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', $this->testUser->getBalance() - 0.05 + 0.02),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodWrongSign(ApiTester $I)
    {
        $request = [
            'space' => $this->space,
            'login' => $this->testUser->getUserId(),
            'cmd'   => 'getBalance',
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5(http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'error_sign'
        ]);
    }

    public function testMethodSpaceNotFound(ApiTester $I)
    {
        $request = [
            'cmd'   => 'getBalance',
            'space' => '1',
            'login' => $this->testUser->getUserId(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/aristocrat', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => 'fail',
            'error'     => 'internal_error'
        ]);
    }

}