<?php


namespace Testing\MrSlotty;

use iHubGrid\Accounting\Users\IntegrationUser;

class Params
{
    public $enableMock;

    public $big_amount = 1000000;
    public $amount = 0.01;
    public $winLose = -0.01;
    public $win_amount = 2;
    public $jackpot_amount = 3;
    public $currency = 'EUR';
    public $balance = 1000.34;
    public $gameId = 123;

    public $tradeId = 'dbe5314a6b376ba901721a4c9bc0f4d4';
    public $no_bet_object_id = 12345;

    public $bet_operation_id = 9543958;
    public $win_operation_id = 2834034;
    public $transactionId = 946267;

    public $userId;
    public $login;
    public $wrongUserId = 41234123412343434;
    public $cashdeskId;
    public $partnerId;
    public $userIP = "127.0.0.1";


    public function __construct($integration)
    {
        $this->enableMock = env('ENABLE_ACCOUNT_MANAGER_MOCK') ?? true;
        $this->login = (int)env('TEST_USER_ID') . '--1---5--127-0-0-1';
        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = (int)env('TEST_CASHEDESK');
        $this->partnerId = (int)env('TEST_PARTNER_ID');
        $this->serviceId = (int)config("integrations.{$integration}.service_id");
    }

    public function getTradeId()
    {
        if ($this->enableMock) {
            return $this->tradeId;
        }

        return md5(microtime());
    }

    public function getBalance()
    {
        if ($this->enableMock) {
            return $this->balance;
        }

        return IntegrationUser::get($this->userId, 0, 'tests')->getBalance();
    }
}