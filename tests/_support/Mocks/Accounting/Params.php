<?php


namespace Testing\Accounting;

use iHubGrid\Accounting\Users\IntegrationUser;

class Params
{
    public $enableMock;

    private $balance = 1000.00;
    public $currency = 'EUR';

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
        $this->serviceId = ($integration)
            ? (int)config("integrations.{$integration}.service_id") : 0;
    }

    public function getBalance()
    {
        if ($this->enableMock) {
            return $this->balance;
        }

        return IntegrationUser::get($this->userId, 0, 'tests')->getBalance();
    }

    public function getBalanceInCents(): int
    {
        return 100 * $this->getBalance();
    }
}