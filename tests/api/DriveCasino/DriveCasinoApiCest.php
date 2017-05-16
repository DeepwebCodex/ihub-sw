<?php

use App\Models\DriveCasinoProdObjectIdMap;
use Testing\DriveMedia\AccountManagerMock;
use Testing\DriveMedia\Params;

class DriveCasinoApiCest
{
    private $space;

    /** @var Params  */
    private $params;

    public function _before()
    {
        $this->space = config('integrations.drivecasino.spaces.FUN.id');
        $this->key = config('integrations.drivecasino.spaces.FUN.key');

        $this->params = new Params('drivecasino');
    }

    public function testMethodBalance(ApiTester $I)
    {
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
        $I->sendPOST('/drivecasino', $request);
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
        $tradeId = md5(microtime());
        $objectId = DriveCasinoProdObjectIdMap::getObjectId($tradeId);
        $bet = 1;
        $winLose = -1;
        (new AccountManagerMock($this->params))->bet($objectId, $bet)->mock($I);
        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->params->login,
            'bet'       => $bet,
            'winLose'   => $winLose,
            'tradeId'   => $tradeId,
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
            'login'     => $this->params->login,
            'balance'   => money_format('%i', ($this->params->balance - $bet)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

}