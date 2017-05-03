<?php

use App\Models\InspiredVirtualGaming\EventLink;

class InspiredVirtualGamingApiCest
{
    const URL = '/ivg';

    const ID_PREFIX_COUNTER = 50000000;

    protected $eventId;

    public function _before()
    {
        $this->eventId = $this->getEventId();
    }

    public function _after()
    {
        $this->clearEvent($this->eventId);
    }

    /**
     * @skip
     */
    public function testInvalidMethod(ApiTester $I)
    {
        $I->disableMiddleware();
        $I->sendPOST(self::URL);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContains('BADFORMAT');
    }

    /**
     * @skip
     */
    public function testMainSuccess(ApiTester $I)
    {
        $I->disableMiddleware();

        $I->expect('try to post new event');

        $I->sendPOST(self::URL, $this->getEventCardData($this->eventId));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseContains('ACK');

        $I->expect('try to suspend this event bets');

        $I->sendPOST(self::URL, $this->getNoMoreBetsData($this->eventId));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseContains('ACK');

        $I->expect('try to send results for this event');

        $I->sendPOST(self::URL, $this->getResultData($this->eventId));
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseContains('ACK');
    }

    private function getEventCardData(int $eventId) : array
    {
        return [
            'events' => [
                'event' => [
                        [
                        'wdls' => [
                            'wdl' => [
                                [
                                    'Num' => 1,
                                    'Outcome' => 'WIN',
                                    'Price' => '3.09'
                                ],
                                [
                                    'Num' => 2,
                                    'Outcome' => 'DRAW',
                                    'Price' => '3.11'
                                ],
                                [
                                    'Num' => 3,
                                    'Outcome' => 'LOSE',
                                    'Price' => '1.98'
                                ]
                            ],
                            'NumBets' => 3
                        ],
                        'EventId' => $eventId,
                        'EventType' => 4,
                        'EventTime' => date('Y-m-d H:i:s'),
                        'EventName' => 'IHub test league',
                        'OddsType'  => 'decimal',
                        'Team1' => 'IHub: Команда1',
                        'Team2' => 'IHub: Команда2'
                    ]
                ],
                'NumEvents' => 1
            ],
            'MessageType' => 'EventCard',
            'MessageDateTime' => date('Y-m-d H:i:s'),
            'ControllerId' => 101
        ];
    }

    private function getNoMoreBetsData(int $eventId) : array
    {
        return [
            'event' => [
                'EventId' => $eventId,
                'EventType' => 4,
                'EventTime' => date('Y-m-d H:i:s')
            ],
            'MessageType' => 'NoMoreBets',
            'MessageDateTime' => date('Y-m-d H:i:s'),
            'ControllerId' => 101
        ];
    }

    private function getResultData(int $eventId) : array
    {
        return [
            'event' => [
                'EventId' => $eventId,
                'EventType' => 4,
                'EventTime' => date('Y-m-d H:i:s'),
                'Result' => '1-2'
            ],
            'MessageType' => 'Result',
            'MessageDateTime' => date('Y-m-d H:i:s'),
            'ControllerId' => 101
        ];
    }

    /**
     * @return mixed
     */
    protected function getEventId()
    {
        return EventLink::getLastId() + self::ID_PREFIX_COUNTER + random_int(0, 1000);
    }


    protected function clearEvent($eventId)
    {
        /**@var EventLink $model*/
        $model = EventLink::where('event_id_ivg', $eventId);

        if($model) {
            $model->delete();
        }
    }
}
