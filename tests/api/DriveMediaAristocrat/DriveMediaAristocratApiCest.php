<?php

use DriveMedia\TestUser;

class DriveMediaAristocratApiCest
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

    public function testMethodBalance(ApiTester $I)
    {
        $request = [
            'space' => $this->space,
            'login' => $this->testUser->getUserId(),
            'cmd'   => 'getBalance',
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
            'balance'   => money_format('%i', $this->testUser->getBalance()),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->testUser->getUserId(),
            'bet'       => '0.05',
            'winLose'   => '-0.05',
            'tradeId'   => (string)rand(1111111111111,9999999999999).'_'.rand(111111111,999999999),
            'betInfo'   => 'Bet',
            'gameId'    => '123',
            'matrix'    => 'EAGLE,DINGO,BOAR,BOAR,BOAR,;TEN,JACK,KING,QUEEN,TEN,;DINGO,BOAR,DINGO,DINGO,SCATTER,;',
            'WinLines'  => 0,
            'date'      => time()
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
            'balance'   => money_format('%i', $this->testUser->getBalance() - 0.05),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}