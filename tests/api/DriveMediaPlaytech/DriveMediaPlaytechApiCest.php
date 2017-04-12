<?php

use DriveMedia\TestUser;

class DriveMediaPlaytechApiCest
{
    private $key;
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before() {
        $this->key = config('integrations.DriveMediaPlaytech.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaPlaytech.spaces.FUN.id');

        $this->testUser = new TestUser();
    }

    public function testMethodBalance(ApiTester $I)
    {
        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => $this->testUser->getUserId(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
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
            'bet'       => '1.0',
            'winLose'   => '-1.0',
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', ($this->testUser->getBalance() - 1.0)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}