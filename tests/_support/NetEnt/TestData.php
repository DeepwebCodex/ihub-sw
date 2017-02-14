<?php

namespace NetEnt;

use App\Components\Integrations\NetEnt\Hmac;
use App\Components\Users\IntegrationUser;

class TestData
{
    const AMOUNT = 1;
    /**
     * @var IntegrationUser
     */
    private $userId;
    private $currency;
    private $amount;

    public function __construct(TestUser $testUser)
    {
        $user = $testUser->getUser();
        $this->userId = $user->id;
        $this->currency = $user->getCurrency();
        $this->amount = self::AMOUNT;
    }

    public function notFound()
    {
        return $this->basic('not_found');
    }

    public function roundInfo()
    {
        return $this->basic('roundinfo');
    }

    public function ping()
    {
        return $this->basic('ping');
    }


    public function getBalance()
    {
        $params = [
            'type' => 'balance',
            'userid' => $this->userId,
            'currency' => $this->currency,
            'i_gameid' => '123',
            'i_extparam' => '2323',
            'i_gamedesc' => '3434',
            'i_actionid' => '4545',
        ];
        $params['hmac'] = (new Hmac($params))->get();

        return $params;
    }

    public function bet($game_number = null, $transferId = null)
    {
        $transfer_id = ($transferId) ? $transferId : md5(time() + rand(1000, 2000));
        $params = [
            'type' => 'debit',
            'tid' => ''.$transfer_id,
            'userid' => $this->userId,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'i_gameid' => $game_number ?? $this->getUniqueNumber(),
            'i_extparam' => '',
            'i_gamedesc' => '',
        ];
        $params['hmac'] = (new Hmac($params))->get();

        return $params;
    }

    public function win($game_number = null, $transferId = null)
    {
        $transfer_id = ($transferId) ? $transferId : md5(time() + rand(2000, 3000));
        $params = [
            'type' => 'credit',
            'tid' => ''.$transfer_id,
            'userid' => $this->userId,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'i_gameid' => $game_number ?? $this->getUniqueNumber(),
            'i_extparam' => '',
            'i_gamedesc' => '',
        ];
        $params['hmac'] = (new Hmac($params))->get();

        return $params;
    }

    public function renewHmac(array $params)
    {
        unset($params['hmac']);
        $params['hmac'] = (new Hmac($params))->get();

        return $params;
    }
//
//    public function srcRoundInfo($game_number)
//    {
//        $params = [
//            'type' => 'roundinfo',
//            'gameid' => $game_number,
//            'userid' => self::PLAYER_ID,
//            'actions' => [
//                'actid' => '',
//                'type' => 'bet',
//                'amount' => '',
//                'timestamp' => '',
//            ],
//            'i_gamedesc' => '',
//        ];
//        $params['hmac'] = $this->netent_func->generateToken($params);
//        return $params;
//    }

    protected function getUniqueNumber()
    {
        return time() + mt_rand(1, 10000);
    }


    public function authFailed()
    {
        return $this->getBalance();
    }

    public function account()
    {
        return $this->basic('get_account_details');
    }

    public function refreshToken()
    {
        return $this->basic('refresh_token');
    }

    public function newToken()
    {
        return $this->basic('request_new_token');
    }

    public function setAmount($amount)
    {
        return $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function resetAmount()
    {
        return $this->amount = self::AMOUNT;
    }

    private function basic($method)
    {
        $params = [
            'type' => $method,
        ];
        $params['hmac'] = (new Hmac($params))->get();

        return $params;
    }
}