<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/6/16
 * Time: 3:46 PM
 */

namespace Testing;


use App\Components\ExternalServices\RemoteSession;
use App\Components\Integrations\GameSession\GameSessionService;
use Mockery;

class GameSessionsMock
{
    public static function getMock(){
        $game_session = Mockery::mock(GameSessionService::class);

        $game_session->shouldReceive('start');
        $game_session->shouldReceive('get')->withArgs(['user_id'])->andReturn(env('TEST_USER_ID'));
        $game_session->shouldReceive('get')->withArgs(['currency'])->andReturn("EUR");
        $game_session->shouldReceive('regenerate')->andReturn("e4fda8473f68894a11c99acc25ecca11");

        return $game_session;
    }
}