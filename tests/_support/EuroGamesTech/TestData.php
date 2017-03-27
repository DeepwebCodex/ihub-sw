<?php

namespace EuroGamesTech;

use App\Components\Users\IntegrationUser;
use Testing\Params;

class TestData
{
    const IS_MOCK = true;

    private $userId;
    private $currency;
    private $amount;
    private $amount_backup;
    public $bigAmount;
    public $winAmount;

    public function __construct()
    {
        $this->userId = (int)env('TEST_USER_ID');
        $this->currency = Params::CURRENCY;
        $this->amount_backup =
        $this->amount = Params::AMOUNT * 100;
        $this->winAmount = Params::WIN_AMOUNT * 100;
        $this->jackpotAmount = Params::JACKPOT_AMOUNT * 100;
        $this->bigAmount = Params::BIG_AMOUNT * 100;
    }

    public function authenticate($simple = true)
    {
        return $this->basic($simple);
    }

    public function getBalance($simple = true)
    {
        return array_merge($this->basic($simple), [
            'Currency' => $this->currency,
            'GameId' => random_int(1, 500),
        ]);
    }

    public function bet($simple = true)
    {
        return array_merge($this->transaction($simple), ['Reason' => 'ROUND_BEGIN']);
    }

    public function win($gameNumber = null, $simple = true)
    {
        $data = array_merge($this->transaction($simple), ['Reason' => 'ROUND_END']);
        if ($gameNumber) {
            $data['GameNumber'] = $gameNumber;
        }

        return $data;
    }

    public function betWin($simple = true)
    {
        return array_merge($this->transaction($simple), [
            'WinAmount' => $this->winAmount,
            'Reason' => 'ROUND_END']);
    }

    public function betLost($simple = true)
    {
        return array_merge($this->transaction($simple), [
            'WinAmount' => 0,
            'Reason' => 'ROUND_END']);
    }

    public function setAmount($amount)
    {
        return $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }


    private function basic($simple = true)
    {
        return array_merge($this->getCompoundId($simple), [
            'UserName' => config('integrations.egt.UserName'),
            'Password' => config('integrations.egt.Password'),
            'PortalCode' => $this->currency,
            'SessionId' => md5(str_random())
        ]);
    }

    private function getCompoundId($simple = true)
    {
        $data = [];
        if ($simple === true) {
            $data['PlayerId'] = $this->userId;
        } else {
            $data['PlayerId'] = $this->userId;
            $data['PartnerId'] = env('TEST_PARTNER_ID');
            $data['CashdeskId'] = env('TEST_CASHEDESK');
            $data['UserIp']     = '127.0.0.1';
        }

        return $data;
    }

    private function transaction($simple = true)
    {
        return array_merge($this->basic($simple), [
            'Currency' => $this->currency,
            'GameId' => random_int(1, 500),
            'TransferId' => md5(str_random()),
            'GameNumber' => $this->getObjectId(),
            'Amount' => $this->amount,
        ]);
    }

    private function getObjectId()
    {
        return (self::IS_MOCK) ? Params::OBJECT_ID : time() + mt_rand(1, 10000);
    }
}