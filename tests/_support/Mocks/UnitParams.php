<?php

namespace Testing;


class UnitParams
{
    public $enableMock;

    public $serviceId;
    public $userId;
    public $cashdeskId;
    public $partnerId;
    public $userIP = "127.0.0.1";


    public function __construct($integration = null)
    {
        $this->enableMock = env('ACCOUNT_MANAGER_MOCK_IS_ENABLED') ?? true;

        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = (int)env('TEST_CASHEDESK');
        $this->partnerId = (int)env('TEST_PARTNER_ID');
    }
}