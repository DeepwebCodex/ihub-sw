<?php

namespace NetEntertainment;

use iHubGrid\Accounting\Users\IntegrationUser;

class TestUser
{
    public function getUser()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), config('integrations.netEntertainment.service_id'), 'netEntertainment');
    }

    public function getBalance()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), config('integrations.netEntertainment.service_id'), 'netEntertainment')->getBalance();
    }

    public function getCurrency()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), config('integrations.netEntertainment.service_id'), 'netEntertainment')->getCurrency();
    }
}