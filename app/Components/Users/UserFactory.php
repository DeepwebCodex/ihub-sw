<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 5:18 PM
 */

namespace App\Components\Users;


use App\Components\ExternalServices\AccountManager;
use App\Components\Users\Exceptions\UserGetException;
use App\Components\Users\Exceptions\UserServiceException;
use App\Components\Users\Exceptions\UserWalletException;
use App\Components\Users\Interfaces\UserInterface;
use App\Exceptions\Api\ApiHttpException;

/**
 * @property  AccountManager $accountManager
 */
class UserFactory
{
    private $accountManager;

    private $userData;

    public function make(int $userId, int $serviceId, string $className, string $integration) : IntegrationUser
    {
        $this->accountManager = $this->getAccountManager();


        $this->getUser($userId);

        /**@var UserInterface $user*/
        $user = new $className($this->userData, $integration);

        if(!$user->validateService($serviceId)){
            throw new UserServiceException(400, "Invalid service", 5001);
        }

        if(!$user->getActiveWallet()){
            throw new UserWalletException(400, "Invalid wallet" , 5002);
        }

        return $user;
    }

    /**
     * @param int $userId
     */
    private function getUser(int $userId){
        $this->userData = $this->accountManager->getUserInfo($userId);
    }

    /**
     * @return AccountManager
     */
    private function getAccountManager(){
        return app('AccountManager');
    }
}