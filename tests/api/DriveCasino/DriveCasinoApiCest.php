<?php

use DriveMedia\TestUser;

class DriveCasinoApiCest
{
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before()
    {
        $this->space = config('integrations.drivecasino.spaces.FUN.id');
        $this->key = config('integrations.drivecasino.spaces.FUN.key');

        $this->testUser = new TestUser();
    }

    /**
     * @skip
     */
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
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', $this->testUser->getBalance()),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    /**
     * @skip
     */
    public function testMethodBet(ApiTester $I)
    {
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->testUser->getUserId(),
            'bet'       => 1,
            'winLose'   => -1,
            'tradeId'   => md5(microtime()),
            'betInfo'   => 'bet',
            'gameId'    => '183',
            'matrix'    => 0,
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', ($this->testUser->getBalance() - 1)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

}