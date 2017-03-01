<?php

namespace NetEntertainment;

use App\Components\Integrations\NetEntertainment\Hmac;
use Testing\Params;

class TestData
{
    const IS_MOCK = true;

    private $userId;
    private $currency;
    private $amount;
    private $amount_backup;
    public $bigAmount;
    public $gameId;

    public function __construct()
    {
        $this->userId = (int)env('TEST_USER_ID');
        $this->currency = Params::CURRENCY;
        $this->amount_backup =
        $this->amount = Params::AMOUNT;
        $this->bigAmount = Params::BIG_AMOUNT;
        $this->game_id = Params::GAME_ID;
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
            'i_gameid' => $this->game_id,
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

        return (self::IS_MOCK) ? Params::OBJECT_ID : time() + mt_rand(1, 10000);
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
        return $this->amount = $this->amount_backup;
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