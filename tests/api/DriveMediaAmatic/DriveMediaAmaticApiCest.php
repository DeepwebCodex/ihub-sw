<?php

use iHubGrid\Accounting\ExternalServices\AccountManager;

use Testing\DriveMediaAmatic\AccountManagerMock;
use Testing\DriveMediaAmatic\Params;

class DriveMediaAmaticApiCest
{
    private $key;
    private $space;

    /** @var  Params */
    private $params;

    public function _before()
    {
        $this->key = config('integrations.DriveMediaAmatic.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAmatic.spaces.FUN.id');

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
        $I->sendPOST('/amatic', $request);
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
            'space'     => $this->space,
            'login'     => $this->params->login,
            'cmd'       => 'writeBet',
            'bet'       => $this->params->amount,
            'winLose'   => -$this->params->amount,
            'tradeId'   => $this->params->getTradeId(),
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', ($this->params->balance - $this->params->amount)),
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