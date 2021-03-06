<?php

use App\Models\DriveMediaPlaytechProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveMediaPlaytechApiCest
{
    private $key;
    private $space;

    /** @var Params  */
    private $params;

    /** @var Helper  */
    private $helper;

    public function _before() {
        $this->key = config('integrations.DriveMediaPlaytech.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaPlaytech.spaces.FUN.id');

        $this->params = new Params('DriveMediaPlaytech');
        $this->helper = new Helper($this->params);
    }

    public function testMethodBalance(ApiTester $I)
    {
        $balance = $this->params->getBalance();

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
        $I->sendPOST('/playtech', $request);
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
        $tradeId = md5(microtime());
        $objectId = DriveMediaPlaytechProdObjectIdMap::getObjectId($tradeId);
        $bet = 1.0;
        $winLose = -1.0;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)
            ->mock($I);

        $request = [
            'cmd'       => 'writeBet',
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
            'betInfo'   => 'spin',
            'gameId'    => '183',
            'matrix'    => '[]',
            'WinLines'  => 0,
            'date'      => time(),
        ];

        $request = array_merge($request, [
            'sign'  => strtoupper(md5($this->key . http_build_query($request)))
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/playtech', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', $balance - $bet),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}