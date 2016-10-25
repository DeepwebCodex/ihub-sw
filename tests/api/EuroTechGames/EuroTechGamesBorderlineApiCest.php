<?php

use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;

class EuroTechGamesBorderlineApiCest
{

    private $gameNumber;
    private $options;

    public function _before()
    {
        $this->options = config('integrations.egt');
    }

    public function _after()
    {
    }

    // tests
    public function testNoBetWin(ApiTester $I)
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
            'Reason'    => 'ROUND_END'
        ];

        $I->disableMiddleware();
        $I->sendPOST('/egt/Deposit', array_merge($request, [
            'DefenceCode' => \App\Components\Integrations\EuroGamesTech\EgtHelper::generateDefenceCode($request['PlayerId'], $request['PortalCode'], time())
        ]));
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Bet was not placed</ErrorMessage>");

        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testStoragePending(ApiTester $I)
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

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_PENDING,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => \App\Models\ObjectIdMap::getObjectId($this->gameNumber, array_get($this->options, 'service_id')),
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/egt/Withdraw', array_merge($request, [
            'DefenceCode' => \App\Components\Integrations\EuroGamesTech\EgtHelper::generateDefenceCode($request['PlayerId'], $request['PortalCode'], time())
        ]));
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>3000</ErrorCode>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_PENDING,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testTransactionDuplicate(ApiTester $I)
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
            'Reason'    => 'ROUND_BEGIN'
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => \App\Models\ObjectIdMap::getObjectId($this->gameNumber, array_get($this->options, 'service_id')),
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/egt/Withdraw', array_merge($request, [
            'DefenceCode' => \App\Components\Integrations\EuroGamesTech\EgtHelper::generateDefenceCode($request['PlayerId'], $request['PortalCode'], time())
        ]));
        $I->seeResponseCodeIs(409);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1100</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>Transaction duplicate</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$testUser->getBalanceInCents()}</Balance>");
    }

    public function testZeroWin(ApiTester $I)
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
            'Amount'    => 0,
            'Reason'    => 'ROUND_END'
        ];

        Transactions::create([
            'operation_id' => $I->grabService('AccountManager')->getFreeOperationId(),
            'user_id' => env('TEST_USER_ID'),
            'service_id' => array_get($this->options, 'service_id'),
            'amount' => 10/100,
            'move'  => TransactionRequest::D_WITHDRAWAL,
            'partner_id' => request()->server('PARTNER_ID'),
            'cashdesk' => request()->server('FRONTEND_NUM'),
            'status' => TransactionRequest::STATUS_COMPLETED,
            'currency' => $testUser->getCurrency(),
            'foreign_id' => array_get($request, 'TransferId'),
            'object_id' => \App\Models\ObjectIdMap::getObjectId($this->gameNumber, array_get($this->options, 'service_id')),
            'transaction_type' => TransactionRequest::TRANS_BET
        ]);

        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => array_get($request, 'TransferId'),
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);

        $I->disableMiddleware();
        $I->sendPOST('/egt/Deposit', array_merge($request, [
            'DefenceCode' => \App\Components\Integrations\EuroGamesTech\EgtHelper::generateDefenceCode($request['PlayerId'], $request['PortalCode'], time())
        ]));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsXml();
        $I->expect('min required items in response');
        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
        $I->seeXmlResponseIncludes("<Balance>{$testUser->getBalanceInCents()}</Balance>");

        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(\App\Models\Transactions::class, [
            'foreign_id' => $request['TransferId'],
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testMultiWin(ApiTester $I)
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
        $I->sendPOST('/egt/WithdrawAndDeposit', array_merge($request, [
            'DefenceCode' => \App\Components\Integrations\EuroGamesTech\EgtHelper::generateDefenceCode($request['PlayerId'], $request['PortalCode'], time())
        ]));
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
            'Reason'    => 'JACKPOT_END'
        ];

        $I->sendPOST('/egt/Deposit', array_merge($request, [
            'DefenceCode' => \App\Components\Integrations\EuroGamesTech\EgtHelper::generateDefenceCode($request['PlayerId'], $request['PortalCode'], time())
        ]));
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
            'transaction_type' => TransactionRequest::TRANS_BONUS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }
}