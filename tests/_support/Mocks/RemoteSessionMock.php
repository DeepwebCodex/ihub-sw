<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/6/16
 * Time: 3:46 PM
 */

namespace Testing;

use App\Components\ExternalServices\AccountManager;
use Mockery;

class RemoteSessionMock
{
    public static function getMock(){
        $remote_session = Mockery::mock(RemoteSession::class);

        $remote_session->shouldReceive('start')->andReturnSelf();
        $remote_session->shouldReceive('get')->withArgs(['user_id'])->andReturn(1);
        $remote_session->shouldReceive('getSessionId')->andReturn("KSKHDU95jG34");

        return $remote_session;
    }
}