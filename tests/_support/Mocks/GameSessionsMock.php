<?php
namespace Testing;

use iHubGrid\SeamlessWalletCore\GameSession\Exceptions\SessionDoesNotExist;
use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Mockery;
use Mockery\Mock;
use function env;

/**
 * Class GameSessionsMock
 * @package Testing
 */
class GameSessionsMock
{

    /** @var Mockery\Mock $game_session */
    public static function getMock($game_session = null)
    {
        if (!$game_session) {
            /** @var Mockery\Mock $game_session */
            $game_session = Mockery::mock(GameSessionService::class);
        }

        $game_session->shouldReceive('start')->once();
        $game_session->shouldReceive('start')->withArgs(['authorization_must_fails'])->andThrow(new SessionDoesNotExist());
        $game_session->shouldReceive('get')->withArgs(['user_id'])->andReturn(env('TEST_USER_ID'));
        $game_session->shouldReceive('get')->withArgs(['created'])->andReturn(time());
        $game_session->shouldReceive('get')->withArgs(['currency'])->andReturn("EUR");
        $game_session->shouldReceive('get')->withArgs(['userIp'])->andReturn("127.0.0.1");

        $game_session->shouldReceive('getSessionIdByContext')->andReturn("e4fda8473f68894a11c99acc25ecca11");

        $game_session->shouldReceive('get')->withArgs(['partner_id'])->andReturn(env('TEST_PARTNER_ID'));

        $game_session->shouldReceive('get')->withArgs(['cashdesk_id'])->andReturn(env('TEST_CASHEDESK'));
        $game_session->shouldReceive('get')->withArgs(['game_id'])->andReturn(Params::GAME_ID);
        $game_session->shouldReceive('regenerate')->andReturn("e4fda8473f68894a11c99acc25ecca11");
        $game_session->shouldReceive('prolong');
        $game_session->shouldReceive('store')->once();
        $game_session->shouldReceive('getData')->once();
        $game_session->shouldReceive('create')->once();
        $game_session->shouldReceive('getStorageKey')->once()->withAnyArgs()->andReturn('e4fda8473f68894a11c99acc25ecca11');

        return $game_session;
    }
}
