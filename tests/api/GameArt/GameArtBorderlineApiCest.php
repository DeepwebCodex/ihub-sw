<?php

namespace tests\api\GameArt;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Testing\GameArt\AccountManagerMock;
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
        $this->mockAccountManager($I, (new AccountManagerMock())->userNotFound()->get());

        $request = [
            'action' => 'balance',
            'remote_id' => $this->params->wrongUserId,
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
        //TODO: fix 404 response code with AM mock
//        $I->seeResponseCodeIs(404);
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
        $this->mockAccountManager($I, (new AccountManagerMock())->get());

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

    private function mockAccountManager(\ApiTester $I, $mock)
    {
        if($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }
    }

}