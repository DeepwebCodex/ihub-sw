<?php


namespace Testing\GameArt;

class Params
{
    public $enableMock = true;

    public $big_amount = 1000000;
    public $amount = 0.10;
    public $win_amount = 2;
    public $jackpot_amount = 3;
    public $currency = 'EUR';
    public $balance = 100.34;
    public $game_id = 123;

    public $object_id = 1234;
    public $no_bet_object_id = 12345;

    public $bet_operation_id = 9543958;
    public $transactionId = 946267;

    public $userId;
    public $wrongUserId = 234234565465465454;
    public $cashdeskId;
    public $partnerId;
    public $userIP = "127.0.0.1";
    public $action = '/gameart';


    public function __construct()
    {
        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = 1; //(int)env('TEST_CASHEDESK');
        $this->partnerId = 1; //(int)env('TEST_PARTNER_ID');
        $this->serviceId = (int)config('integrations.gameart.service_id');
        $this->options = config('integrations.gameart');
    }

    public function getObjectId()
    {
        if ($this->enableMock) {
            return $this->object_id;
        }

        return random_int(100000, 9900000);
    }

    public function getTransactionId(): int
    {
        if ($this->enableMock) {
            return $this->transactionId;
        }

        // substr(time(), 1, 9)
        return $this->getUniqueId();
    }

    private function getUniqueId(): int
    {
        return round(microtime(true)) + mt_rand(1, 10000);
    }
}