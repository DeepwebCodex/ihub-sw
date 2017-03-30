<?php

use DriveMedia\TestUser;

class DriveMediaAmaticApiCest
{
    private $key;
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before()
    {
        $this->key = config('integrations.DriveMediaAmatic.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAmatic.spaces.FUN.space');

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
        $I->sendPOST('/amatic', $request);
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
            'space'     => $this->space,
            'login'     => $this->testUser->getUserId(),
            'cmd'       => 'writeBet',
            'bet'       => '0.1',
            'winLose'   => '-0.1',
            'tradeId'   => (string)microtime(),
            'betInfo'   => 'bet',
            'gameId'    => '183',
            'matrix'    => '[6,5,3,6,1,8,7,5,4]',
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/amatic', $request);
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', ($this->testUser->getBalance() - 0.1)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}