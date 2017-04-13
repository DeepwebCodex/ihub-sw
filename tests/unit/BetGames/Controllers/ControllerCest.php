<?php

namespace unit\BetGames\Controllers;


use App\Components\Formatters\BetGamesApiFormatter;
use App\Http\Controllers\Api\BetGamesController;

class ControllerCest
{
    public function testResponseLatin(\UnitTester $I)
    {
        $c = new BetGamesController((new BetGamesApiFormatter()));

        $params = [
            'user_id' => 1,
            'username' => 'ФІВАВІАВФІА',
            'currency' => 'єєвіааіф',
            'info' => 'цукее',
//            'info' => '北京',
        ];
        $response = $c->prepareResponse('', '', $params);
        $I->assertTrue(ctype_alpha($response['params']['username']));
        $I->assertTrue(ctype_alpha($response['params']['info']));
    }

    public function testResponseParams(\UnitTester $I)
    {
        $c = new BetGamesController((new BetGamesApiFormatter()));

        $params = [
            'user_id' => 1,
            'username' => 'test_player',
            'currency' => 'EUR',
            'info' => 'second name',
        ];

        $response = $c->prepareResponse(123213, 23, $params);

        $I->assertInternalType('string', $response['method']);
        $I->assertInternalType('string', $response['token']);
        $I->assertTrue(in_array($response['success'], [0,1]));
        $I->assertInternalType('integer', $response['error_code']);
        $I->assertInternalType('string', $response['error_text']);
        $I->assertInternalType('array', $response['params']);
    }
}