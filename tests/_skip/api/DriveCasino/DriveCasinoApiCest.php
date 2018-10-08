<?php

use App\Models\DriveCasinoProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveCasinoApiCest
{
    private $space;

    /** @var Params  */
    private $params;

    /** @var Helper  */
    private $helper;

    public function _before()
    {
        $this->space = config('integrations.drivecasino.spaces.FUN.id');
        $this->key = config('integrations.drivecasino.spaces.FUN.key');

        $this->params = new Params('drivecasino');
        $this->helper = new Helper($this->params);
    }

    public function testMethodBalance(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $request = [
            'cmd'   => 'getBalance',
            'space' => $this->space,
            'login' => $this->helper->getLogin(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/drivecasino', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', $this->params->getBalance()),
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
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
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
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', ($balance - $bet)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

}