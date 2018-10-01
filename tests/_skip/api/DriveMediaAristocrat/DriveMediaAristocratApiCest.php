<?php

use App\Models\DriveMediaAristocratProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveMediaAristocratApiCest
{
    private $key;
    private $space;
    private $route;

    /** @var  Params */
    private $params;

    /** @var Helper  */
    private $helper;


    public function _before() {
        $this->key = config('integrations.DriveMediaAristocrat.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAristocrat.spaces.FUN.id');
        $this->route = '/aristocrat';

        $this->params = new Params('DriveMediaAristocrat');
        $this->helper = new Helper($this->params);
    }

    public function testMethodBalance(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $balance = $this->params->getBalance();
        $request = [
            'space' => $this->space,
            'login' => $this->helper->getLogin(),
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
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', $balance),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $tradeId = (string)rand(1111111111111,9999999999999).'_'.rand(111111111,999999999);
        $objectId = DriveMediaAristocratProdObjectIdMap::getObjectId($tradeId);
        $bet = 0.05;
        $winLose = -0.05;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
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
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', $balance - $bet),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}