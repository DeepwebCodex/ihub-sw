<?php

use App\Components\Users\IntegrationUser;
use DriveMedia\TestUser;

class DriveMediaNovomaticApiCest
{
    const URI = '/novomatic';

    const TEST_SPACE = '1807';

    const TEST_GAME_ID = 132;

    const BET_AMOUNT = '0.01';

    /** @var  TestUser $testUser */
    private $testUser;

    public function _before() {
        $this->testUser = new TestUser();
    }

    public function testGetBalance(ApiTester $I)
    {
        $requestData = [
            'cmd' => 'getBalance',
            'space' => self::TEST_SPACE,
            'login' => $this->testUser->getUserId(),
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => $this->testUser->getUserId(),
            'balance' => (string)round($this->testUser->getBalance(), 2),
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
        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => $this->testUser->getUserId(),
            'bet' => self::BET_AMOUNT,
            'winLose' => '-' . self::BET_AMOUNT,
            'tradeId' => md5(microtime()),
            'betInfo' => 'spin',
            'gameId' => self::TEST_GAME_ID,
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => $this->testUser->getUserId(),
            'balance' => (string)round($this->testUser->getBalance() - self::BET_AMOUNT, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => $this->testUser->getUserId(),
            'bet' => self::BET_AMOUNT,
            'winLose' => self::BET_AMOUNT,
            'tradeId' => md5(microtime()),
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
            'login' => $this->testUser->getUserId(),
            'balance' => (string)round($this->testUser->getBalance() - (float)self::BET_AMOUNT + (float)self::BET_AMOUNT, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }
}
