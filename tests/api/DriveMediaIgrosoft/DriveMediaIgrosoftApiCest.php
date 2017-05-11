<?php

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Testing\DriveMediaIgrosoft\AccountManagerMock;
use Testing\DriveMediaIgrosoft\Params;

class DriveMediaIgrosoftApiCest
{
    private $key;
    private $space;

    /** @var  Params */
    private $params;

    public function _before() {
        $this->key = config('integrations.DriveMediaIgrosoft.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaIgrosoft.spaces.FUN.id');

        $this->params = new Params();
    }

    public function testMethodBalance(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->get());

        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => $this->params->login,
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/igrosoft', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $this->params->balance),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->bet()->win()->get());
        $tradeId = md5(time());

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$this->params->amount,
            'winLose'   => (string)$this->params->winLose,
            'tradeId'   => $this->params->getTradeId(),
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', ($this->params->balance - $this->params->amount)),
            'status'    => 'success',
            'error'     => ''
        ]);

        //WIN
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$this->params->amount,
            'winLose'   => (string)$this->params->winLose,
            'tradeId'   => $this->params->getTradeId(),
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
            'login'     => $this->params->login,
            //TODO:
//            'balance'   => money_format('%i', ($this->params->balance - $this->params->amount + $this->params->winLose)),
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