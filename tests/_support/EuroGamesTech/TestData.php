<?php

namespace EuroGamesTech;

use Testing\Params;

class TestData
{
    private $isMock;

    private $userId;
    private $currency;
    private $amount;
    private $amount_backup;
    public $bigAmount;
    public $winAmount;

    public function __construct()
    {
        $this->isMock = env('ACCOUNT_MANAGER_MOCK_IS_ENABLED') ?? true;

        $this->userId = (int)env('TEST_USER_ID');
        $this->currency = Params::CURRENCY;
        $this->amount_backup =
        $this->amount = Params::AMOUNT * 100;
        $this->winAmount = Params::WIN_AMOUNT * 100;
        $this->jackpotAmount = Params::JACKPOT_AMOUNT * 100;
        $this->bigAmount = Params::BIG_AMOUNT * 100;
    }

    public function authenticate()
    {
        return $this->basic();
    }

    public function getBalance()
    {
        return array_merge($this->basic(), [
            'Currency' => $this->currency,
            'GameId' => random_int(1, 500),
        ]);
    }

    public function bet()
    {
        return array_merge($this->transaction(), ['Reason' => 'ROUND_BEGIN']);
    }

    public function win($gameNumber = null)
    {
        $data = array_merge($this->transaction(), ['Reason' => 'ROUND_END']);
        if ($gameNumber) {
            $data['GameNumber'] = $gameNumber;
        }

        return $data;
    }

    public function betWin()
    {
        return array_merge($this->transaction(), [
            'WinAmount' => $this->winAmount,
            'Reason' => 'ROUND_END']);
    }

    public function betLost()
    {
        return array_merge($this->transaction(), [
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


    private function basic()
    {
        return [
            'PlayerId' => $this->userId,
            'UserName' => config('integrations.egt.UserName'),
            'Password' => config('integrations.egt.Password'),
            'PortalCode' => $this->currency,
            'SessionId' => Params::SESSION_ID, //md5(str_random())
            'DefenceCode' => Params::DEFENCE_CODE
        ];
    }

    private function transaction()
    {
        return array_merge($this->basic(), [
            'Currency' => $this->currency,
            'GameId' => random_int(1, 500),
            'TransferId' => md5(str_random()),
            'GameNumber' => $this->getObjectId(),
            'Amount' => $this->amount,
        ]);
    }

    private function getObjectId()
    {
        return ($this->isMock) ? Params::OBJECT_ID : time() + mt_rand(1, 10000);
    }
}