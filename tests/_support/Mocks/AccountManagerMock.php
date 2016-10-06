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

class AccountManagerMock
{
    public static function getMock(){
        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager->shouldReceive('getUserInfo')->andReturn([]);

        return $accountManager;
    }
}