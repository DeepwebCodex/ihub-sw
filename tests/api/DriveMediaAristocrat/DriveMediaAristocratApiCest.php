<?php

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Testing\DriveMediaAristocrat\AccountManagerMock;
use Testing\DriveMediaAristocrat\Params;

class DriveMediaAristocratApiCest
{
    private $key;
    private $space;
    private $route;

    /** @var  Params */
    private $params;


    public function _before() {
        $this->key = config('integrations.DriveMediaAristocrat.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAristocrat.spaces.FUN.id');
        $this->route = '/aristocrat';

        $this->params = new Params();
    }

    public function testMethodBalance(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->get());

        $request = [
            'space' => $this->space,
            'login' => $this->params->login,
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $this->params->balance),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->bet()->get());
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$this->params->amount,
            'winLose'   => (string)$this->params->winLose,
            'tradeId'   => $this->params->getTradeId(),
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $this->params->balance - $this->params->amount + $this->params->winLose),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    private function mockAccountManager(\ApiTester $I, $mock)
    {
        if($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }
    }
}