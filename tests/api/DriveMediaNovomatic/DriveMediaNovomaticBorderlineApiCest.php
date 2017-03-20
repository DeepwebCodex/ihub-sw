<?php

use App\Components\Users\IntegrationUser;

class DriveMediaNovomaticBorderlineApiCest
{
    const URI = '/novomatic';

    const TEST_SPACE = '1807';

    const TEST_GAME_ID = 132;

    const BET_AMOUNT = '0.01';

    public function testGetBalanceUserNotFound(ApiTester $I)
    {
        $requestData = [
            'cmd' => 'getBalance',
            'space' => self::TEST_SPACE,
            'login' => '41234123412343434',
        ];
        $this->addSignatureToRequestData($requestData);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 'fail',
            'error' => 'user_not_found'
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

    public function testGetBalanceErrorSign(ApiTester $I)
    {
        $testUser = $this->getTestUser();

        $requestData = [
            'cmd' => 'getBalance',
            'space' => self::TEST_SPACE,
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
            'sign' => '123'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 'fail',
            'error' => 'error_sign'
        ]);
    }

    public function testWriteBetUserNotFound(ApiTester $I)
    {
        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => '41234123412343434',
            'bet' => '0.00',
            'winLose' => '0.01',
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
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 'fail',
            'error' => 'user_not_found'
        ]);
    }

    public function testWriteBetErrorSign(ApiTester $I)
    {
        $testUser = $this->getTestUser();

        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
            'bet' => '0.00',
            'winLose' => '0.01',
            'tradeId' => md5(microtime()),
            'betInfo' => 'spin',
            'gameId' => self::TEST_GAME_ID,
            'matrix' => '[]',
            'WinLines' => 0,
            'date' => time(),
            'sign' => '123'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(self::URI, $requestData);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 'fail',
            'error' => 'error_sign'
        ]);
    }

    /*public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = $this->getTestUser();
        $requestData = [
            'cmd' => 'writeBet',
            'space' => self::TEST_SPACE,
            'login' => (string)$testUser->id . "--1--1--127-0-0-1",
            'bet' => '0.00',
            'winLose' => '0.01',
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
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson([
            'status' => 'fail',
            'error' => 'internal_error'
        ]);
    }*/
}
