<?php

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Testing\DriveMedia\AccountManagerMock;
use Testing\DriveMedia\Params;

class DriveMediaNovomaticApiCest
{
    const URI = '/novomatic';

    const TEST_SPACE = '1807';

    const TEST_GAME_ID = 132;

    /** @var  Params */
    private $params;

    public function _before() {
        $this->params = new Params('DriveMediaNovomatic');
    }

    public function testGetBalance(ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->get());

        $requestData = [
            'cmd' => 'getBalance',
            'space' => self::TEST_SPACE,
            'login' => $this->params->login,
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => $this->params->login,
            'balance' => (string)round($this->params->balance, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    protected function addSignatureToRequestData(&$requestData)
    {
        $signatureMaker = new \App\Components\Integrations\DriveMediaNovomatic\SignatureMaker();
        $signature = $signatureMaker->make(self::TEST_SPACE, $requestData);
        $requestData = array_merge($requestData, ['sign' => $signature]);
    }

    public function testBet(ApiTester $I)
    {
        $this->mockAccountManager($I,
            (new AccountManagerMock())
            ->bet($this->params->object_id, $this->params->amount)
            ->get());

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => $this->params->login,
            'bet' => $this->params->amount,
            'winLose' => '-' . $this->params->amount,
            'tradeId' => $this->params->getTradeId(),
            'betInfo' => 'spin',
            'gameId' => self::TEST_GAME_ID,
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => $this->params->login,
            'balance' => (string)round($this->params->balance - $this->params->amount, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $this->mockAccountManager($I,
            (new AccountManagerMock())
                ->bet($this->params->object_id, $this->params->amount)
                ->win($this->params->object_id, $this->params->amount)
                ->get());

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => $this->params->login,
            'bet' => $this->params->amount,
            'winLose' => $this->params->amount,
            'tradeId' => $this->params->getTradeId(),
            'betInfo' => 'spin',
            'gameId' => self::TEST_GAME_ID,
            'matrix' => '[]',
            'WinLines' => 0,
            'date' => time(),
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'login' => $this->params->login,
            'balance' => (string)round($this->params->balance - (float)$this->params->amount + (float)$this->params->amount, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    private function mockAccountManager(\ApiTester $I, $mock)
    {
        if ($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }
    }
}
