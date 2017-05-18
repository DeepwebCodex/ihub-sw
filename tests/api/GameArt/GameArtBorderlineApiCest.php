<?php

namespace tests\api\GameArt;

use Testing\DriveMedia\AccountManagerMock;
use Testing\GameArt\Params;

class GameArtBorderlineApiCest
{
    private $options;
    private $currency;

    /** @var  Params */
    private $params;
    private $action;

    public function _before() {
        $this->params = new Params();
        $this->params->options = config('integrations.gameart');

    }

    public function testMethodUserNotFound(\ApiTester $I)
    {
        $wrongUserId = 234234565465465454;
        (new AccountManagerMock($this->params))->userNotFound($wrongUserId)->mock($I);

        $request = [
            'action' => 'balance',
            'remote_id' => $wrongUserId,
            'remote_data' => json_encode([
                'partner_id' => $this->params->partnerId,
                'cashdesk_id' => $this->params->cashdeskId,
                'user_ip' => $this->params->userIP,
                'currency' => $this->params->currency
            ])
        ];

        $key = [
            'key' => hash('sha1', $this->params->options[$this->params->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET($this->params->action, $request);
        $I->seeResponseCodeIs(404);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '500',
            'msg'       => 'Account not found.',
        ]);

    }

    public function testMethodWrongKey(\ApiTester $I)
    {

        $request = [
            'action' => 'balance',
            'remote_id' => $this->params->userId,
            'remote_data' => json_encode([
                'partner_id' => $this->params->partnerId,
                'cashdesk_id' => $this->params->cashdeskId,
                'user_ip' => $this->params->userIP,
                'currency' => $this->params->currency
            ])
        ];

        $key = [
            'key' => hash('sha1', http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET($this->params->action, $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '500',
            'msg'       => 'Invalid key',
        ]);
    }

    public function testMethodWinWithoutBet(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))->mock($I);

        $request = [
            'action' => 'credit',
            'action_type' => 'WIN',
            'round_id' => substr(time(), 1, 9),
            'remote_id' => $this->params->userId,
            'amount' => 0.20,
            'game_id' => 34,
            'transaction_id' => substr(time(), 1, 9),
            'remote_data' => json_encode([
                'partner_id' => $this->params->partnerId,
                'cashdesk_id' => $this->params->cashdeskId,
                'user_ip' => $this->params->userIP,
                'currency' => $this->params->currency
            ])
        ];

        $key = [
            'key' => hash('sha1', $this->params->options[$this->params->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET($this->params->action, $request);
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '500',
            'msg'       => 'Bet was not placed',
        ]);
    }
}