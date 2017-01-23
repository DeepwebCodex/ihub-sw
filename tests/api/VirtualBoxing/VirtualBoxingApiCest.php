<?php

use App\Components\Integrations\VirtualBoxing\ProgressService;
use App\Models\VirtualBoxing\EventLink;

class VirtualBoxingApiCest
{
    const URI_PREFIX = '/vb/';

    const SUCCESS_RESPONSE_TEXT = 'Done';

    const DUPLICATE_RESPONSE_TEXT = 'duplicate';

    const ID_PREFIX_COUNTER = 50000000;

    protected $eventId;

    public function testInvalidMethod(ApiTester $I)
    {
        $I->sendGET(self::URI_PREFIX . 'test');
        $I->seeResponseCodeIs(400);
        $I->seeResponseContains('Method not found');
    }

    public function testMainSuccess(ApiTester $I)
    {
        $this->eventId = $this->getEventId();

        $this->methodMatchBet($I);

        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);
        $I->seeResponseCodeIs(200);

        $this->methodMatchProgress($I, ProgressService::STATUS_CODE_NO_MORE_BETS);

        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);
        $I->seeResponseCodeIs(200);

        $this->methodMatchProgress($I, ProgressService::STATUS_CODE_FINISHED_EVENT);

        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);
        $I->seeResponseCodeIs(200);

        $this->methodResult($I);

        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);
        $I->seeResponseCodeIs(200);

        $this->clearEvent($this->eventId);
    }

    /**
     * @return mixed
     */
    protected function getEventId()
    {
        return EventLink::getLastVbId() + self::ID_PREFIX_COUNTER + random_int(0, 1000);
    }

    /**
     * @param $eventVbId
     */
    protected function clearEvent($eventVbId)
    {
        EventLink::where('event_vb_id', $eventVbId)->delete();
    }

    /**
     * @param ApiTester $I
     */
    protected function methodMatchBet(ApiTester $I)
    {
        $request = [
            'name' => 'match_bet',
            'match' => [
                'scheduleId' => $this->eventId,
                'competition' => 'ihub: Test competition',
                'bet' => [
                    'code' => 'R5',
                    'selection' => [
                        [
                            'home' => 'H',
                            'name' => 'ihub: Boxer 1',
                            'price' => [
                                'dec' => 2.97
                            ]
                        ]
                    ]
                ],
                'away' => 'ihub: Away player',
                'home' => 'ihub: Home player',
                'location' => 'ihub: Test location',
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
                'name' => 'ihub: Test match name',
            ]
        ];

        $I->disableMiddleware();
        $I->sendPOST(self::URI_PREFIX, $request);
    }

    /**
     * @param ApiTester $I
     * @param $statusCode
     */
    protected function methodMatchProgress(ApiTester $I, $statusCode)
    {
        $request = [
            'name' => 'match_progress',
            'event_id' => $this->eventId,
            'mnem' => 'MB',
            'xu:ups-at' => [
                'xu:at' => [
                    [
                        '#text' => $statusCode
                    ]
                ]
            ]
        ];

        $I->disableMiddleware();
        $I->sendPOST(self::URI_PREFIX, $request);
    }

    /**
     * @param ApiTester $I
     */
    protected function methodResult(ApiTester $I)
    {
        $rounds = [];
        for ($i = 1; $i < 7; ++$i) {

            $statusVariants = ['1', '2', 'Draw'];
            $roundStatus = $statusVariants[array_rand($statusVariants)];

            $pointsValueFirst = 0;
            $pointsValueSecond = 0;
            if ($roundStatus === '1') {
                $pointsValueFirst = 1;
            } elseif ($roundStatus === '2') {
                $pointsValueSecond = 1;
            }

            $knockdownVariants = [0, 1, 2];
            $knockdownValue = $knockdownVariants[array_rand($knockdownVariants)];

            $knockdownValueFirst = 0;
            $knockdownValueSecond = 0;
            if ($knockdownValue === 1) {
                $knockdownValueFirst = 1;
            } elseif ($knockdownValue === 2) {
                $knockdownValueSecond = 1;
            }

            $rounds[] = [
                'round' => $i,
                'status' => $roundStatus,
                'participant' => [
                    [
                        'id' => '2',
                        'knockdown' => $knockdownValueSecond,
                        'point' => $pointsValueSecond,
                    ],
                    [
                        'id' => '1',
                        'knockdown' => $knockdownValueFirst,
                        'point' => $pointsValueFirst,
                    ]
                ]
            ];
        }

        $request = [
            'type' => 'result',
            'result' => [
                'event_id' => $this->eventId,
                'tid' => md5($this->eventId),
                'round' => $rounds,
            ]
        ];

        $I->disableMiddleware();
        $I->sendPOST(self::URI_PREFIX, $request);
    }

    public function testDuplicateBet(ApiTester $I)
    {
        $this->eventId = $this->getEventId();

        $this->methodMatchBet($I);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->methodMatchBet($I);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::DUPLICATE_RESPONSE_TEXT);

        $this->clearEvent($this->eventId);
    }

    public function testDuplicateResult(ApiTester $I)
    {
        $this->eventId = $this->getEventId();

        $this->methodMatchBet($I);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->methodMatchProgress($I, ProgressService::STATUS_CODE_NO_MORE_BETS);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->methodMatchProgress($I, ProgressService::STATUS_CODE_FINISHED_EVENT);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->methodResult($I);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->methodResult($I);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::DUPLICATE_RESPONSE_TEXT);

        $this->clearEvent($this->eventId);
    }

    public function testCancelEvent(ApiTester $I)
    {
        $this->eventId = $this->getEventId();

        $this->methodMatchBet($I);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->methodMatchProgress($I, ProgressService::STATUS_CODE_CANCELLED_EVENT);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains(self::SUCCESS_RESPONSE_TEXT);

        $this->clearEvent($this->eventId);
    }
}
