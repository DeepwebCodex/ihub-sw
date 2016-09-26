<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 12:27 PM
 */

namespace App\Components\Users\Decorators;


use App\Components\Users\IntegrationUser;

class UserBalanceCents
{
    private $user;

    public function __construct(IntegrationUser $user) {
        $this->user = $user;
    }

    public function getBalanceInCents(){
        $balance = $this->user->getBalance();

        if($balance !== null){
            return $balance * 100;
        }

        return null;
    }
}