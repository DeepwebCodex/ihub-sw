<?php

namespace EuroGamesTech;

use App\Components\Users\IntegrationUser;

class TestData
{
    /**
     * @var IntegrationUser
     */
    private $user;
    private $amount;

    public function __construct(TestUser $testUser)
    {
        $this->user = $testUser->getUser();
        $this->amount = 10;
    }

    public function authenticate()
    {
        return $this->basic();
    }

    public function getBalance()
    {
        return array_merge($this->basic(), [
            'Currency' => $this->user->getCurrency(),
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
            'WinAmount' => $this->amount,
            'Reason' => 'ROUND_END']);
    }

    public function betLost()
    {
        return array_merge($this->transaction(), [
            'WinAmount' => 0,
            'Reason' => 'ROUND_END']);
    }

    public function getAmount()
    {
        return $this->amount;
    }


    private function basic()
    {
        return [
            'UserName' => config('integrations.egt.UserName'),
            'Password' => config('integrations.egt.Password'),
            'PlayerId' => $this->user->id,
            'PortalCode' => $this->user->getCurrency(),
            'SessionId' => md5(str_random())
        ];
    }

    private function transaction()
    {
        return array_merge($this->basic(), [
            'Currency' => $this->user->getCurrency(),
            'GameId' => random_int(1, 500),
            'TransferId' => md5(str_random()),
            'GameNumber' => random_int(100000, 9900000),
            'Amount' => $this->amount,
        ]);
    }
}