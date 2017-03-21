<?php

namespace DriveMedia;

use \App\Components\Users\IntegrationUser;

class TestUser {
    private $user;

    public function __construct()
    {
        $this->user = IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
    }

    public function getUser() {
        return $this->user;
    }

    public function getBalanceInCents() {
        return $this->user->getBalanceInCents();
    }

    public function getBalance() {
        return $this->user->getBalance();
    }

    public function getCurrency() {
        return $this->user->getCurrency();
    }

    public function getUserId()
    {
        return $this->user->id . '--1---5--127-0-0-1';
    }

}
