<?php

use App\Components\Transactions\TransactionRequest;
use \App\Components\Integrations\EuroGamesTech\EgtHelper;
use \App\Components\Integrations\EuroGamesTech\StatusCode;
use \App\Components\Integrations\EuroGamesTech\DefenceCode;

class EuroTechGamesApiCest
{

    private $gameNumber;
    private $defenceCode;

    public function _before()
    {

    }

    public function _after()
    {
    }

    // tests
    public function testMethodNotFound(ApiTester $I)
    {
        $I->sendGET('/egt');
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsXml();
        $I->expect('both items are in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Server error</ErrorMessage>");
    }

    public function testMethodAuthenticate(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'UserName' => 'FavbetEGTSeamless',
            'Password' => '6IQLjj8Jowe3X',
            'PlayerId' => $testUser->id,
            'PortalCode' => $testUser->getCurrency(),
            'SessionId' => md5(str_random())
        ];

        $I->disableMiddleware();
        $this->defenceCode = (new DefenceCode())->generate($request['PlayerId'], $request['PortalCode'], time());
        $I->sendPOST('/egt/Authenticate', array_merge($request, ['DefenceCode' => $this->defenceCode]));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$testUser->getBalanceInCents()}</Balance>");
    }

    /**
     * @depends testMethodAuthenticate
     * @param ApiTester $I
     */
    public function testDefenceCodeDuplicate(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'UserName' => 'FavbetEGTSeamless',
            'Password' => '6IQLjj8Jowe3X',
            'PlayerId' => $testUser->id,
            'PortalCode' => $testUser->getCurrency(),
            'SessionId' => md5(str_random())
        ];
        $I->disableMiddleware();
        $I->sendPOST('/egt/Authenticate', array_merge($request, [ 'DefenceCode' => $this->defenceCode ]));
        $response = (array)(new SimpleXMLElement($I->grabResponse()));
        $I->assertEquals(StatusCode::EXPIRED, $response['ErrorCode']);
    }

    public function testMethodGetPlayerBalance(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'UserName' => 'FavbetEGTSeamless',
            'Password' => '6IQLjj8Jowe3X',
            'PlayerId' => $testUser->id,
            'PortalCode' => $testUser->getCurrency(),
            'Currency' => $testUser->getCurrency(),
            'GameId' => random_int(1, 500),
            'SessionId' => md5(str_random())
        ];

        $I->disableMiddleware();
        $I->sendPOST('/egt/GetPlayerBalance', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$testUser->getBalanceInCents()}</Balance>");
    }

    public function testMethodWithdraw(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $this->gameNumber = random_int(100000, 9900000);

        $request = [
            'UserName' => 'FavbetEGTSeamless',
            'Password' => '6IQLjj8Jowe3X',
            'PlayerId' => $testUser->id,
            'PortalCode' => $testUser->getCurrency(),
            'Currency' => $testUser->getCurrency(),
            'GameId' => random_int(1, 500),
            'SessionId' => md5(str_random()),
            'TransferId' => md5(str_random()),
            'GameNumber' => $this->gameNumber,
            'Amount'    => 10,
            'Reason'    => 'ROUND_BEGIN'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/egt/Withdraw', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $expectedBalance = $testUser->getBalanceInCents()-10;
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testMethodDeposit(ApiTester $I)
    {
        $this->testMethodWithdraw($I);

        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'UserName' => 'FavbetEGTSeamless',
            'Password' => '6IQLjj8Jowe3X',
            'PlayerId' => $testUser->id,
            'PortalCode' => $testUser->getCurrency(),
            'Currency' => $testUser->getCurrency(),
            'GameId' => random_int(1, 500),
            'SessionId' => md5(str_random()),
            'TransferId' => md5(str_random()),
            'GameNumber' => $this->gameNumber,
            'Amount'    => 10,
            'Reason'    => 'ROUND_END'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/egt/Deposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $expectedBalance = $testUser->getBalanceInCents()+10;
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testWithdrawAndDeposit(ApiTester $I)
    {
        $this->gameNumber = random_int(100000, 9900000);

        $testUser = \App\Components\Users\IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'UserName' => 'FavbetEGTSeamless',
            'Password' => '6IQLjj8Jowe3X',
            'PlayerId' => $testUser->id,
            'PortalCode' => $testUser->getCurrency(),
            'Currency' => $testUser->getCurrency(),
            'GameId' => random_int(1, 500),
            'SessionId' => md5(str_random()),
            'TransferId' => md5(str_random()),
            'GameNumber' => $this->gameNumber,
            'Amount'    => 10,
            'WinAmount' => 10,
            'Reason'    => 'ROUND_END'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/egt/WithdrawAndDeposit', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->expect('unchanged balance after operation');
        $expectedBalance = $testUser->getBalanceInCents();
        $I->seeXmlResponseIncludes("<Balance>{$expectedBalance}</Balance>");

        $I->expect('Can see record of both transactions applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testJackpot(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST('/internal/egt/jackpot/set');
        $I->sendPOST('/internal/egt/jackpot/get');
        $I->seeResponseIsJson();
    }
}