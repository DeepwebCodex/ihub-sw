<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/6/16
 * Time: 3:46 PM
 */

namespace Testing;


use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\GameSession\GameSessionService;
use Mockery;

class GameSessionsMock
{
    public static function getMock(){
        /** @var Mockery\Mock $game_session */
        $game_session = Mockery::mock(GameSessionService::class);

        $game_session->shouldReceive('start')->once();
        $game_session->shouldReceive('start')->withArgs(['authorization_must_fails'])->andThrow(new SessionDoesNotExist());
        $game_session->shouldReceive('get')->withArgs(['user_id'])->andReturn(env('TEST_USER_ID'));
        $game_session->shouldReceive('get')->withArgs(['created'])->andReturn(time());
        $game_session->shouldReceive('get')->withArgs(['currency'])->andReturn("EUR");

        $game_session->shouldReceive('get')->withArgs(['partner_id'])->andReturn(env('TEST_PARTNER_ID'));

        $game_session->shouldReceive('get')->withArgs(['cashdesk_id'])->andReturn(env('TEST_CASHEDESK'));
        $game_session->shouldReceive('regenerate')->andReturn("e4fda8473f68894a11c99acc25ecca11");
        $game_session->shouldReceive('prolong');

        return $game_session;
    }
}