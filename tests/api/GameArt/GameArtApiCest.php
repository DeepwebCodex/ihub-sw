<?php
namespace tests\api\GameArt;

use iHubGrid\Accounting\Users\IntegrationUser;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;

class GameArtApiCest
{
    private $options;
    private $currency;
    private $action = 'gameart';

    /** @var  Params */
    private $params;

    public function _before() {
        $this->params = new Params('gameart');
        $this->options = config('integrations.gameart');
    }

    public function testBalance(\ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

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
            'key' => hash('sha1', $this->options[$this->params->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET($this->action, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '200',
            'balance'   => self::toFloat($testUser->getBalanceInCents()),
        ]);

    }

    public function testBet(\ApiTester $I)
    {
        $amount = 0.10;
        $roundId = substr(time(), 1, 9);
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($roundId, $amount, $balance - $amount)
            ->mock($I);

        $testUser = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');

        $request = [
            'action' => 'debit',
            'action_type' => 'BET',
            'round_id' => $roundId,
            'remote_id' => $this->params->userId,
            'amount' => $amount,
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
            'key' => hash('sha1', $this->options[$this->params->currency] . http_build_query($request))
        ];

        $request = array_merge($request, $key);

        $I->sendGET($this->action, $request);
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson([
            'status'    => '200',
            'balance'   => self::toFloat(100 * $balance - 100 * $amount),
        ]);
    }

    protected function toFloat(int $balance)
    {
        $balance /= 100;
        return number_format($balance, 2, '.', '');
    }
}