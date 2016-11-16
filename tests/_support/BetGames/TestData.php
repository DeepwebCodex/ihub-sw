<?php

namespace BetGames;

use App\Components\Users\IntegrationUser;
use App\Components\Integrations\BetGames\Signature;

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

    public function getFor($method)
    {
        $data = $this->$method();

        $s = new Signature($data);
    }

    public function ping()
    {
        return [
            'method' => 'ping',
            'token' => '-',
            'time' => time(),
            'params' => null,
        ];
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

    public function getAmount()
    {
        return $this->amount;
    }


    private function basic()
    {
        return [
            'method' => config('integrations.egt.UserName'),
            'token' => config('integrations.egt.Password'),
            'time' => $this->user->id,
            'params' => '',
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