<?php


namespace Testing\DriveMediaAmatic;

use App\Models\DriveMediaAmaticProdObjectIdMap;

class Params
{
    public $enableMock;

    public $big_amount = 1000000;
    public $amount = 0.10;
    public $win_amount = 2;
    public $jackpot_amount = 3;
    public $currency = 'EUR';
    public $balance = 100.34;
    public $game_id = 123;

    public $tradeId = '0.21700600 1493813131';
    public $object_id;
    public $no_bet_object_id = 12345;

    public $bet_operation_id = 9543958;
    public $win_operation_id = 2834034;
    public $transactionId = 946267;

    public $userId;
    public $login;
    public $wrongUserId = 234234565465465454;
    public $cashdeskId;
    public $partnerId;
    public $userIP = "127.0.0.1";
    public $action = '/gameart';


    public function __construct()
    {
        $this->enableMock = true;
        $this->login = (int)env('TEST_USER_ID') . '--1---5--127-0-0-1';
        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = (int)env('TEST_CASHEDESK');
        $this->partnerId = (int)env('TEST_PARTNER_ID');
        $this->serviceId = (int)config('integrations.DriveMediaAmatic.service_id');
        $this->options = config('integrations.gameart');
        $this->object_id = DriveMediaAmaticProdObjectIdMap::getObjectId($this->tradeId);
    }

    public function getTradeId()
    {
        if ($this->enableMock) {
            return $this->tradeId;
        }

        return (string)microtime();
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