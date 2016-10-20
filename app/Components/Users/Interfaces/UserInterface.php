<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 5:28 PM
 */

namespace App\Components\Users\Interfaces;


interface UserInterface
{
    public function getBalance();

    public function getCurrency();

    public function getActiveWallet();

    public function validateService(int $serviceId);

    public function isTestUser();
}