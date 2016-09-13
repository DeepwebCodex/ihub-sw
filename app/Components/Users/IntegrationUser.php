<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 10:43 AM
 */

namespace App\Components\Users;

use App\Components\Users\Exceptions\UserCurrencyException;
use App\Components\Users\Interfaces\UserInterface;
use App\Components\Users\Traits\SessionCurrency;


class IntegrationUser implements UserInterface
{
    use SessionCurrency;

    private $attributes = [];

    private $wallets    = [];
    private $documents  = [];
    private $services   = [];

    /**
     * @param int $userId
     * @param int $serviceId
     * @param string $integration
     * @return IntegrationUser
     */
    public static function get(int $userId, int $serviceId, string $integration){
        return (new UserFactory())->make($userId, $serviceId, self::class, $integration);
    }

    public function __construct(array $userData, string $integration)
    {
        $this->load($userData);
        $this->redisKey = env('REDIS_PREFIX', 'app') . ':' . $integration . ':users:';
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }

    public function getAttributes(){
        return $this->attributes;
    }

    public function getBalance(){
        $activeWallet = $this->getActiveWallet();
        if($activeWallet){
            return $activeWallet->deposit;
        }

        return null;
    }

    public function storeSessionCurrency($currency){
        $this->setSessionCurrency($currency, $this->id);
    }

    public function checkSessionCurrency(){
        if(!$this->validateSessionCurrency($this->getCurrency(), $this->id)){
            throw new UserCurrencyException(409, "Currency mismatch", 1401);
        }
    }

    public function getCurrency(){
        $activeWallet = $this->getActiveWallet();
        if($activeWallet){
            return $activeWallet->currency;
        }

        return null;
    }

    /**
     * @return Wallet
     */
    public function getActiveWallet(){
        if($this->wallets){
            foreach ($this->wallets as $wallet){
                /**@var Wallet $wallet*/
                if($wallet->is_active){
                    return $wallet;
                }
            }
        }

        return null;
    }

    /**
     * @param int $serviceId
     * @return bool
     */
    public function validateService(int $serviceId){
        if($this->services){
            foreach ($this->services as $service){
                /**@var Service $service*/
                if($service->service_id == $serviceId){
                    if($service->is_enabled && !$service->is_blocked){
                        return !$this->isTestUser();
                    }
                }
            }
        }

        return false;
    }

    public function isTestUser(){
        if(app()->environment() === 'production' && isset($this->group) && $this->group == 4){
            return true;
        }

        return false;
    }

    private function load(array $data){

        $fillAttributes = [];

        foreach ($data as $name => $value){
            if(is_array($value)){
                foreach ($value as $model) {
                    if(isset($model['__record'])) {
                        switch ($model['__record']) {
                            case 'document':
                                $this->documents[] = new Document($model);
                                break;
                            case 'wallet':
                                $this->wallets[] = new Wallet($model);
                                break;
                            case 'user_service':
                                $this->services[] = new Service($model);
                                break;
                            default:
                                break;
                        }
                    }
                }
            } else {
                $fillAttributes[$name] = $value;
            }
        }

        $this->attributes = $fillAttributes;
    }


}