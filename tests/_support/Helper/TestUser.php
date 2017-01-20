<?php

namespace Helper;

use \App\Components\Users\IntegrationUser;

class TestUser {

    private $userId;
    private $user;

    function __construct($userId = '') {
        if (!$userId) {
            $this->userId = env('TEST_USER_ID');
        } else {
            $this->userId = $userId;
        }
        
        $this->user = IntegrationUser::get($this->userId, 0, 'tests');
    }

    public function getUser() {
        return $this->user;
    }

    public function getBalanceInCents() {
        return $this->user->getBalanceInCents();
    }

    public function getCurrency() {
        return $this->user->getCurrency();
    }

}
