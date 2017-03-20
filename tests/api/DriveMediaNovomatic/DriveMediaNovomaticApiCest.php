<?php

use App\Components\Users\IntegrationUser;

class DriveMediaNovomaticApiCest
{
    const URI = '/novomatic';

    const TEST_SPACE = '1807';

    const TEST_GAME_ID = 132;

    const BET_AMOUNT = '0.01';

    public function testGetBalance(ApiTester $I)
    {
        $testUser = $this->getTestUser();

        $requestData = [
            'cmd' => 'getBalance',
            'space' => self::TEST_SPACE,
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
            'balance' => (string)round($testUser->getBalance(), 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    protected function getTestUser()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
    }

    protected function addSignatureToRequestData(&$requestData)
    {
        $signatureMaker = new \App\Components\Integrations\DriveMediaNovomatic\SignatureMaker();
        $signature = $signatureMaker->make(self::TEST_SPACE, $requestData);
        $requestData = array_merge($requestData, ['sign' => $signature]);
    }

    public function testBet(ApiTester $I)
    {
        $testUser = $this->getTestUser();

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
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
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
            'balance' => (string)round($testUser->getBalance() - self::BET_AMOUNT, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $testUser = $this->getTestUser();

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
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
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
            'balance' => (string)round($testUser->getBalance() - (float)self::BET_AMOUNT + (float)self::BET_AMOUNT, 2),
            'status' => 'success',
            'error' => ''
        ]);
    }
}
