<?php

use iHubGrid\Accounting\Users\IntegrationUser;

class MrSlottyApiCest
{
    private $options;
    private $cashDeskId = 1;
    private $partnerId = 1;
    private $userIp = "127.0.0.1";

    public function _before()
    {
        $this->options = config('integrations.mrslotty');
    }

    public function testMethodBalance(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action'   => 'balance',
            'player_id' => (string)$testUser->id,
            'currency' => $testUser->getCurrency(),
        ];
        ksort($request);

        $request = array_merge($request, [
            'hash' => hash_hmac("sha256", http_build_query($request), $this->options['salt'])
        ]);

        $I->sendGET('/mrslotty', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 200,
            'balance' => $testUser->getBalanceInCents(),
            'currency' => $testUser->getCurrency()
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action'   => 'bet',
            'amount' => 100,
            'player_id' => (string)$testUser->id,
            'transaction_id' => (string)time(),
            'currency' => $testUser->getCurrency(),
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => (string)time() . random_int(0, 9),
            'extra' => http_build_query([
                'cashdesk_id' => $this->cashDeskId,
                'partner_id' => $this->partnerId,
                'user_ip' => $this->userIp
            ])
        ];
        ksort($request);

        $request = array_merge($request, [
            'hash' => hash_hmac("sha256", http_build_query($request), $this->options['salt'])
        ]);

        $I->sendGET('/mrslotty', $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 200,
            'balance' => $testUser->getBalanceInCents() - 100,
            'currency' => $testUser->getCurrency()
        ]);
    }

    public function testNoMoney(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action'   => 'bet',
            'amount' => 1000000000000000000,
            'player_id' => (string)$testUser->id,
            'transaction_id' => (string)time(),
            'currency' => $testUser->getCurrency(),
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => (string)time() . random_int(0, 9),
            'extra' => http_build_query([
                'cashdesk_id' => $this->cashDeskId,
                'partner_id' => $this->partnerId,
                'user_ip' => $this->userIp
            ])
        ];
        ksort($request);

        $request = array_merge($request, [
            'hash' => hash_hmac("sha256", http_build_query($request), $this->options['salt'])
        ]);

        $I->sendGET('/mrslotty', $request);
        $I->seeResponseCodeIs(400);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 400,
            'error' => ['code' => \App\Components\Integrations\MrSlotty\StatusCode::NO_MONEY]
        ]);
    }

}