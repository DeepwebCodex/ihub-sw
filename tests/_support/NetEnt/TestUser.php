<?php

namespace NetEntertainment;

use \App\Components\Users\IntegrationUser;

class TestUser
{
    public function getUser()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), config('integrations.netEnt.service_id'), 'netEnt');
    }

    public function getBalance()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), config('integrations.netEnt.service_id'), 'netEnt')->getBalance();
    }

    public function getCurrency()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), config('integrations.netEnt.service_id'), 'netEnt')->getCurrency();
    }
}