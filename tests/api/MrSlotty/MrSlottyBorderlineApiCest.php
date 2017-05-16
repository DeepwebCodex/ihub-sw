<?php

use App\Components\Integrations\MrSlotty\MrSlottyHelper;
use Testing\DriveMedia\AccountManagerMock;
use Testing\MrSlotty\Params;

class MrSlottyBorderlineApiCest
{

    private $options;

    /** @var Params  */
    private $params;

    public function _before()
    {
        $this->options = config('integrations.mrslotty');
        $this->params = new Params('mrslotty');
    }

    public function testMethodWrongHash(ApiTester $I)
    {
        $request = [
            'action'   => 'balance',
            'player_id' => (string)$this->params->userId,
            'currency' => $this->params->currency,
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
        (new AccountManagerMock($this->params))->mock($I);
        $request = [
            'action'   => 'win',
            'amount' => 100,
            'player_id' => (string)$this->params->userId,
            'transaction_id' => (string)time(),
            'currency' => $this->params->currency,
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => (string)time() . random_int(0, 9),
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
        $roundId = (string)time() . random_int(0, 9);
        $objectId = MrSlottyHelper::getObjectId($roundId);
        $amount = 100;
        $win = 200;

        (new AccountManagerMock($this->params))
            ->bet($objectId, MrSlottyHelper::amountCentsToWhole($amount))
            ->win($objectId, MrSlottyHelper::amountCentsToWhole($win))
            ->mock($I);
        $request = [
            'action'   => 'bet_win',
            'amount' => $amount,
            'win' => $win,
            'player_id' => (string)$this->params->userId,
            'bet_transaction_id' => (string)time(),
            'win_transaction_id' => (string)(time() + 1),
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
            'balance' => 100 * ($this->params->balance + MrSlottyHelper::amountCentsToWhole($win)),
            'currency' => $this->params->currency
        ]);
    }

    public  function testMethodBetWin2(ApiTester $I)
    {
        $round_id = (string)time() . random_int(0, 9);
        $objectId = MrSlottyHelper::getObjectId($round_id);
        $amount = 100;
        $win = 200;

        (new AccountManagerMock($this->params))
            ->bet($objectId, MrSlottyHelper::amountCentsToWhole($amount))
            ->win($objectId, MrSlottyHelper::amountCentsToWhole($win))
            ->mock($I);


        $request = [
            'action'   => 'bet',
            'amount' => $amount,
            'player_id' => (string)$this->params->userId,
            'transaction_id' => (string)time(),
            'currency' => $this->params->currency,
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => $round_id,
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
            'balance' => 100 * ($this->params->balance - MrSlottyHelper::amountCentsToWhole($amount)),
            'currency' => $this->params->currency
        ]);

        //WIN
        $request = [
            'action'   => 'win',
            'amount' => $win,
            'player_id' => (string)$this->params->userId,
            'transaction_id' => (string)(time() + 1),
            'currency' => $this->params->currency,
            'type' => 'spin',
            'game_id' => 'game_name',
            'round_id' => $round_id,
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
            'balance' => 100 * ($this->params->balance + MrSlottyHelper::amountCentsToWhole($win)),
            'currency' => $this->params->currency
        ]);
    }
}