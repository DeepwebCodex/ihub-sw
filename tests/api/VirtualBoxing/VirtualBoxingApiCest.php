<?php

class VirtualBoxingApiCest
{
    const URI_PREFIX = '/vb/';

    public function _before()
    {

    }

    public function _after()
    {

    }

    public function testMethodNotFound(ApiTester $I)
    {
        $I->sendGET(self::URI_PREFIX . 'test');
        $I->seeResponseCodeIs(400);
        $I->seeResponseContains('Method not found');

    }

    public function testMethodMatchBet(ApiTester $I)
    {
        $request = [
            'match' => [
                'scheduleId' => '5',
                'competition' => 'Test competition',
                'bet' => [
                    'code' => ''
                ],
                'away' => 'Away player',
                'home' => 'Home player',
                'location' => 'Test location',
                'date' => '2016-11-03',
                'time' => '13:00:00',
                'name' => 'Test match name',
            ]
        ];

        $I->disableMiddleware();
        $I->sendPOST(self::URI_PREFIX, $request);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('done');
    }

    public function testMethodMatchProgress(ApiTester $I)
    {
        $request = [
            'event_id' => '5',
            'mnem' => 'MB',
            'xu:ups-at.xu:at' => [
                '#text' => 'N'
            ]
        ];

        $I->disableMiddleware();
        $I->sendPOST(self::URI_PREFIX, $request);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('done');
    }

    public function testMethodResult(ApiTester $I)
    {
        $request = [
            'type' => 'result',
            'result' => [
                'event_id' => '5',
                'tid' => 'c4ca4238a0b923820dcc509a6f75849b',
                'round' => [

                ],
            ]
        ];

        $I->disableMiddleware();
        $I->sendPOST(self::URI_PREFIX, $request);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('done');
    }
}
