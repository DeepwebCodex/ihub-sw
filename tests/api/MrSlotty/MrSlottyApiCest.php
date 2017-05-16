<?php


use App\Components\Integrations\MrSlotty\MrSlottyHelper;
use Testing\DriveMedia\AccountManagerMock;
use Testing\MrSlotty\Params;

class MrSlottyApiCest
{
    private $options;

    /** @var Params  */
    private $params;

    public function _before()
    {
        $this->options = config('integrations.mrslotty');
        $this->params = new Params('mrslotty');
    }

    public function testMethodBalance(ApiTester $I)
    {
        $balance = $this->params->getBalance();
        (new AccountManagerMock($this->params))->mock($I);
        $request = [
            'action'   => 'balance',
            'player_id' => (string)$this->params->userId,
            'currency' => $this->params->currency,
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
            'balance' => 100 * $balance,
            'currency' => $this->params->currency
        ]);
    }

    public function testMethodBet(ApiTester $I)
    {
        $roundId = (string)time() . random_int(0, 9);
        $objectId = MrSlottyHelper::getObjectId($roundId);
        $amount = 100;
        $balance = $this->params->getBalance();

        (new AccountManagerMock($this->params))
            ->bet($objectId, MrSlottyHelper::amountCentsToWhole($amount))
            ->mock($I);
        $request = [
            'action'   => 'bet',
            'amount' => $amount,
            'player_id' => (string)$this->params->userId,
            'transaction_id' => (string)time(),
            'currency' => $this->params->currency,
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => $roundId,
            'extra' => http_build_query([
                'cashdesk_id' => $this->params->cashdeskId,
                'partner_id' => $this->params->partnerId,
                'user_ip' => $this->params->userIP
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
            'balance' => 100 * $balance - $amount,
            'currency' => $this->params->currency
        ]);
    }

    public function testNoMoney(ApiTester $I)
    {
        $roundId = (string)time() . random_int(0, 9);
        $objectId = MrSlottyHelper::getObjectId($roundId);
        $amount = 1000000000000000000;
        (new AccountManagerMock($this->params))->betExceeded($objectId, MrSlottyHelper::amountCentsToWhole($amount))->mock($I);

        $request = [
            'action'   => 'bet',
            'amount' => $amount,
            'player_id' => (string)$this->params->userId,
            'transaction_id' => (string)time(),
            'currency' => $this->params->currency,
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => $roundId,
            'extra' => http_build_query([
                'cashdesk_id' => $this->params->cashdeskId,
                'partner_id' => $this->params->partnerId,
                'user_ip' => $this->params->userIP
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