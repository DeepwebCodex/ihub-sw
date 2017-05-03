<?php

use iHubGrid\Accounting\Users\IntegrationUser;

use DriveMedia\TestUser;

class DriveMediaIgrosoftApiCest
{
    private $key;
    private $space;

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before() {
        $this->key = config('integrations.DriveMediaIgrosoft.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaIgrosoft.spaces.FUN.id');

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
        $I->sendPOST('/igrosoft', $request);
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
    public function testMethodBetWin(ApiTester $I)
    {
        $tradeId = md5(time());

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->testUser->getUserId(),
            'bet'       => '0.10',
            'winLose'   => '-0.10',
            'tradeId'   => $tradeId,
            'betInfo'   => 'SpinNormal',
            'gameId'    => '183',
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
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', ($this->testUser->getBalance() - 0.10)),
            'status'    => 'success',
            'error'     => ''
        ]);

        //WIN
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->testUser->getUserId(),
            'bet'       => '0.00',
            'winLose'   => '0.50',
            'tradeId'   => $tradeId,
            'betInfo'   => 'CollectWin',
            'gameId'    => '183',
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
            'login'     => $this->testUser->getUserId(),
            'balance'   => money_format('%i', ($this->testUser->getBalance() - 0.10 + 0.50)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}