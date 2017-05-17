<?php

use App\Models\DriveMediaIgrosoftProdObjectIdMap;
use Testing\DriveMedia\AccountManagerMock;
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
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))->mock($I);

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
            'balance'   => money_format('%i', $balance),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $tradeId = md5(time());
        $bet = 0.10;
        $winLose = -0.10;
        $winLose2 = 0.50;
        $objectId = DriveMediaIgrosoftProdObjectIdMap::getObjectId($tradeId);
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->bet($objectId, $bet, $balance - $bet)
            ->win($objectId, $winLose2, $balance - $bet + $winLose2)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $balance - $bet),
            'status'    => 'success',
            'error'     => ''
        ]);

        //WIN
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => '0.0',
            'winLose'   => (string)$winLose2,
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', $balance - $bet + $winLose2),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}