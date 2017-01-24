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

    public function authenticate($simple = true)
    {
        return $this->basic($simple);
    }

    public function getBalance($simple = true)
    {
        return array_merge($this->basic($simple), [
            'Currency' => $this->user->getCurrency(),
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
            'WinAmount' => $this->amount,
            'Reason' => 'ROUND_END']);
    }

    public function betLost($simple = true)
    {
        return array_merge($this->transaction($simple), [
            'WinAmount' => 0,
            'Reason' => 'ROUND_END']);
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
            'PortalCode' => $this->user->getCurrency(),
            'SessionId' => md5(str_random())
        ]);
    }

    private function getCompoundId($simple = true)
    {
        $data = [];
        if ($simple === true) {
            $data['PlayerId'] = $this->user->id;
        } else {
            $data['PlayerId'] = $this->user->id;
            $data['PartnerId'] = env('TEST_PARTNER_ID');
            $data['CashdeskId'] = env('TEST_CASHEDESK');
        }

        return $data;
    }

    private function transaction($simple = true)
    {
        return array_merge($this->basic($simple), [
            'Currency' => $this->user->getCurrency(),
            'GameId' => random_int(1, 500),
            'TransferId' => md5(str_random()),
            'GameNumber' => random_int(100000, 9900000),
            'Amount' => $this->amount,
        ]);
    }
}