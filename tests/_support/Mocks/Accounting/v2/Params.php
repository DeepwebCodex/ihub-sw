<?php


namespace Testing\Accounting\v2;

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
    public $walletData;
    public $paymentInstrumentId;
    public $walletId;
    public $walletAccountId;


    public function __construct($integration = null)
    {
        $this->enableMock = env('ACCOUNT_MANAGER_MOCK_IS_ENABLED') ?? true;
        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = (int)env('TEST_CASHEDESK');
        $this->walletData = [
            0 => [
                'payment_instrument_id' => 3,
                'wallet_id' => "ziwidif@rootfest.net",
                'wallet_account_id' => "EUR",
                'partner_id' => env('TEST_PARTNER_ID')
            ],
            1 => [
                'payment_instrument_id' => 4,
                'wallet_id' => "usd@rootfest.net",
                'wallet_account_id' => "USD",
                'partner_id' => 35
            ]
        ];
        $this->partnerId = $this->walletData[0]['partner_id'];
        $this->paymentInstrumentId = $this->walletData[0]['payment_instrument_id'];
        $this->walletId = $this->walletData[0]['wallet_id'];
        $this->walletAccountId = $this->walletData[0]['wallet_account_id'];
        
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