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

/**
 * @property integer $id
 * @property integer $group
 * @property string $login
 * @property string $email
 * @property string $sess
 * @property string $password
 * @property integer $hash_type
 * @property string $password_hach_old
 * @property string $last_ip
 * @property integer $status_id
 * @property string $first_name
 * @property string $middle_name,
 * @property string $last_name,
 * @property string $lang
 * @property string $timezone
 * @property integer $tzoffset
 * @property string $phone_number
 * @property string $date_of_birth
 * @property string $country_id
 * @property string $city
 * @property string $zip
 * @property string $adress
 * @property string $question
 * @property string $answer
 * @property string $registration_date
 * @property string $title
 * @property integer $cashdesk
 * @property integer $deleted
 * @property integer $trust_level
 * @property boolean $blacklist
 * @property integer $loyalty_rating
 * @property integer $loyalty_points
 * @property integer $loyalty_months
 * @property integer $loyalty_deposit_count
 * @property integer $loyalty_rating_level
 * @property boolean $fav_bet_club_user,
 * @property $coupon
 * @property boolean $mobile_is_active
 * @property boolean $email_is_active
 * @property integer $spam_ok
 * @property integer $partner_id
 * @property string $data
 * @property string $token
 * @property string $oib
 * @property string $nationality
 * @property string $region
 * @property string $fullname
*/
class IntegrationUser implements UserInterface
{
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

    public function getBalanceInCents(){
        $balance = $this->getBalance();

        if($balance !== null){
            return $balance * 100;
        }

        return null;
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