<?php

namespace EuroGamesTech;

use iHubGrid\Accounting\Users\IntegrationUser;

class TestUser
{
    const INTEGRATION = 'egt';
    private $service_id;
    private $user_id;

    public function __construct()
    {
        $this->service_id = config('integrations.egt.service_id');
        $this->user_id = env('TEST_USER_ID');
    }
    public function getUser()
    {
        return IntegrationUser::get($this->user_id, $this->service_id, self::INTEGRATION);
    }

    public function getBalanceInCents()
    {
        return IntegrationUser::get($this->user_id, $this->service_id, self::INTEGRATION)->getBalanceInCents();
    }

    public function getCurrency()
    {
        return IntegrationUser::get($this->user_id, $this->service_id, self::INTEGRATION)->getCurrency();
    }
}