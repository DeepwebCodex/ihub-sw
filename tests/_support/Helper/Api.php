<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use iHubGrid\SeamlessWalletCore\GameSession\GameSessionService;
use Testing\GameSessionsMock;


class Api extends \Codeception\Module
{
    public function mockGameSession(\ApiTester $I)
    {
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }
}
