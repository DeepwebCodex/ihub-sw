<?php

use iHubGrid\Accounting\Users\IntegrationUser;

class MrSlottyBorderlineApiCest
{

    private $options;
    private $cashDeskId = 1;
    private $partnerId = 1;
    private $userIp = "127.0.0.1";

    public function _before()
    {
        $this->options = config('integrations.mrslotty');
    }

    public function testMethodWrongHash(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action'   => 'balance',
            'player_id' => (string)$testUser->id,
            'currency' => $testUser->getCurrency(),
        ];
        ksort($request);

        $request = array_merge($request, [
            'hash' => hash_hmac("sha256", http_build_query($request), "dfghdfj")
        ]);

        $I->sendGET('/mrslotty', $request);
        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 401,
            'error' => [
                'code' => 'ERR006',
                'message' => 'Unauthorized request.'
            ]
        ]);
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action'   => 'win',
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
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 500,
            'error' => [
                'code' => 'ERR001',
                'message' => 'Bet was not placed.'
            ]
        ]);
    }

    public function testMethodBetWin(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action'   => 'bet_win',
            'amount' => 100,
            'win' => 200,
            'player_id' => (string)$testUser->id,
            'bet_transaction_id' => (string)time(),
            'win_transaction_id' => (string)(time() + 1),
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
            'balance' => $testUser->getBalanceInCents() - 100 + 200,
            'currency' => $testUser->getCurrency()
        ]);
    }

    public  function testMethodBetWin2(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $round_id = (string)time() . random_int(0, 9);

        $request = [
            'action'   => 'bet',
            'amount' => 100,
            'player_id' => (string)$testUser->id,
            'transaction_id' => (string)time(),
            'currency' => $testUser->getCurrency(),
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => $round_id,
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

        //WIN
        $request = [
            'action'   => 'win',
            'amount' => 200,
            'player_id' => (string)$testUser->id,
            'transaction_id' => (string)(time() + 1),
            'currency' => $testUser->getCurrency(),
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => $round_id,
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
            'balance' => $testUser->getBalanceInCents() - 100 + 200,
            'currency' => $testUser->getCurrency()
        ]);
    }
}