<?php

use App\Models\DriveMediaAmaticProdObjectIdMap;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveMediaAmaticApiCest
{
    private $key;
    private $space;

    /** @var  Params */
    private $params;

    /** @var Helper  */
    private $helper;

    public function _before()
    {
        $this->key = config('integrations.DriveMediaAmatic.spaces.FUN.key');
        $this->space = config('integrations.DriveMediaAmatic.spaces.FUN.id');

        $this->params = new Params('DriveMediaAmatic');
        $this->helper = new Helper($this->params);
    }

    public function testMethodBalance(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $request = [
            'space' => $this->space,
            'login' => $this->helper->getLogin(),
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
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', $this->params->getBalance()),
            'status'    => 'success',
            'error'     => ''
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $tradeId = (string)microtime();
        $objectId = DriveMediaAmaticProdObjectIdMap::getObjectId($tradeId);
        $bet = 0.1;
        $winLose = -0.1;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($objectId, $bet, $balance - $bet)->win($objectId, $bet, $balance - $bet)
            ->mock($I);

        $request = [
            'space'     => $this->space,
            'login'     => $this->helper->getLogin(),
            'cmd'       => 'writeBet',
            'bet'       => (string)$bet,
            'winLose'   => (string)$winLose,
            'tradeId'   => $tradeId,
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
            'login'     => $this->helper->getLogin(),
            'balance'   => money_format('%i', ($balance - $bet)),
            'status'    => 'success',
            'error'     => ''
        ]);
    }
}