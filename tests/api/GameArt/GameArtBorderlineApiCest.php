<?php

use iHubGrid\Accounting\Users\IntegrationUser;

class GameArtBorderlineApiCest
{
    private $options;
    private $partner_id;
    private $cashdesk_id;
    private $user_ip;
    private $currency;

    public function _before() {
        $this->options = config('integrations.gameart');
        $this->partner_id = 1;
        $this->cashdesk_id = 1;
        $this->user_ip = "127.0.0.1";
        $this->currency = "EUR";
    }

    public function testMethodUserNotFound(ApiTester $I)
    {
        $request = [
            'action' => 'balance',
            'remote_id' => '234234565465465454',
            'remote_data' => json_encode([
                'partner_id' => $this->partner_id,
                'cashdesk_id' => $this->cashdesk_id,
                'user_ip' => $this->user_ip,
                'currency' => $this->currency
            ])
        ];

        $key = [
            'key' => hash('sha1', $this->options[$this->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET('/gameart', $request);
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '500',
            'msg'       => 'Account not found.',
        ]);

    }

    public function testMethodWrongKey(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action' => 'balance',
            'remote_id' => $testUser->id,
            'remote_data' => json_encode([
                'partner_id' => $this->partner_id,
                'cashdesk_id' => $this->cashdesk_id,
                'user_ip' => $this->user_ip,
                'currency' => $this->currency
            ])
        ];

        $key = [
            'key' => hash('sha1', http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET('/gameart', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '500',
            'msg'       => 'Invalid key',
        ]);
    }

    public function testMethodWinWithoutBet(ApiTester $I)
    {
        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action' => 'credit',
            'action_type' => 'WIN',
            'round_id' => substr(time(), 1, 9),
            'remote_id' => $testUser->id,
            'amount' => 0.20,
            'game_id' => 34,
            'transaction_id' => substr(time(), 1, 9),
            'remote_data' => json_encode([
                'partner_id' => $this->partner_id,
                'cashdesk_id' => $this->cashdesk_id,
                'user_ip' => $this->user_ip,
                'currency' => $this->currency
            ])
        ];

        $key = [
            'key' => hash('sha1', $this->options[$this->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET('/gameart', $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '500',
            'msg'       => 'Bet was not placed',
        ]);
    }

}