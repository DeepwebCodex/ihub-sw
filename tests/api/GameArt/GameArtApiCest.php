<?php
namespace tests\api\GameArt;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use iHubGrid\Accounting\Users\IntegrationUser;
use Testing\GameArt\AccountManagerMock;
use Testing\GameArt\Params;

class GameArtApiCest
{
    private $options;
    private $currency;
    private $action;

    /** @var  Params */
    private $params;

    public function _before() {
        $this->params = new Params();
    }

    public function testBalance(\ApiTester $I)
    {
        $this->mockAccountManager($I, (new AccountManagerMock())->get());

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

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
            'key' => hash('sha1', $this->params->options[$this->params->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET($this->params->action, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '200',
            'balance'   => self::toFloat($testUser->getBalanceInCents()),
        ]);

    }

    public function testBet(\ApiTester $I) {

        $this->mockAccountManager($I,
            (new AccountManagerMock())
                ->bet()
                ->get());

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action' => 'debit',
            'action_type' => 'BET',
            'round_id' => $this->params->getObjectId(),
            'remote_id' => $this->params->userId,
            'amount' => $this->params->amount,
            'game_id' => 34,
            'transaction_id' => $this->params->getTransactionId(),
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
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '200',
            'balance'   => self::toFloat($testUser->getBalanceInCents() - 10),
        ]);
    }

    protected function toFloat(int $balance)
    {
        $balance /= 100;
        return number_format($balance, 2, '.', '');
    }

    private function mockAccountManager(\ApiTester $I, $mock)
    {
        if($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $mock);
            $I->haveInstance(AccountManager::class, $mock);
        }
    }

}