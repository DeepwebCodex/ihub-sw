<?php

namespace NetEnt;

use \App\Components\Users\IntegrationUser;

class TestUser
{
    public function getUser()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
    }

    public function getBalance()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests')->getBalance();
    }

    public function getCurrency()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests')->getCurrency();
    }
}