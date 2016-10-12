<?php

use App\Components\Transactions\TransactionRequest;

class EuroTechGamesBorderlineApiCest
{

    private $gameNumber;

    public function _before()
    {

    }

    public function _after()
    {
    }

    // tests
    public function testNoBetWin(ApiTester $I)
    {
        $testUser = \App\Components\Users\IntegrationUser::get(1, 0, 'tests');

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
}