<?php


namespace Testing\MrSlotty;

use iHubGrid\Accounting\Users\IntegrationUser;

class Params
{
    public $enableMock;

    public $currency = 'EUR';
    public $balance = 1000.34;

    public $bet_operation_id = 9543958;
    public $win_operation_id = 2834034;

    public $userId;
    public $wrongUserId = 41234123412343434;
    public $cashdeskId;
    public $partnerId;
    public $userIP = "127.0.0.1";


    public function __construct($integration)
    {
        $this->enableMock = env('ACCOUNT_MANAGER_MOCK_IS_ENABLED') ?? true;

        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = (int)env('TEST_CASHEDESK');
        $this->partnerId = (int)env('TEST_PARTNER_ID');
        $this->serviceId = (int)config("integrations.{$integration}.service_id");
    }

    public function getBalance()
    {
        if ($this->enableMock) {
            return $this->balance;
        }

        return IntegrationUser::get($this->userId, 0, 'tests')->getBalance();
    }
}