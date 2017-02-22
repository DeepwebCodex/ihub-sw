<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Components\ExternalServices\AccountManager;
use App\Components\Integrations\GameSession\GameSessionService;
use Testing\AccountManagerMock;
use Testing\AccountManagerSingleton;
use Testing\GameSessionsMock;
use Testing\Params;


class Api extends \Codeception\Module
{
    public function mockGameSession(\ApiTester $I)
    {
        $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
        $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
    }

    public function mockAccountManager(\ApiTester $I, $service_id, $amount = Params::AMOUNT)
    {
//        $mock = AccountManagerSingleton::getObject($service_id, $amount);
        $mock = (new AccountManagerMock($service_id, $amount))->getMock();
        $I->getApplication()->instance(AccountManager::class, $mock);
        $I->haveInstance(AccountManager::class, $mock);
    }
}
