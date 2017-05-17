<?php


namespace Testing\Orion;

use iHubGrid\Accounting\Users\IntegrationUser;

class Params
{
    public $enableMock;

    public $currency;
    public $balance;
    public $userId;

    public function __construct()
    {
        $this->enableMock = env('ACCOUNT_MANAGER_MOCK_IS_ENABLED') ?? true;
        $this->userId = 10;
        $this->balance = 1000.34;
        $this->currency = 'EUR';
    }

    public function getBalance()
    {
        if ($this->enableMock) {
            return $this->balance;
        }

        return IntegrationUser::get($this->userId, 0, 'tests')->getBalance();
    }
}