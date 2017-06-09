<?php

namespace Fundist;

use App\Components\Integrations\Fundist\Hmac;
use Testing\Accounting\Params;


class TestData
{
    /** @var Params  */
    private $params;

    private $userId;

    public function __construct(string $integration)
    {
        $this->params = new Params('liveDealer');

        $this->userId = (int)env('TEST_USER_ID') . '_' . $this->params->currency;
        $this->integration = $integration;
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
            'currency' => $this->params->currency,
        ];
        $params['hmac'] = (new Hmac($params, $this->integration))->get();

        return $params;
    }

    public function bet($amount = null, $game_number = null, $transferId = null)
    {
        $params = [
            'type' => 'debit',
            'tid' => (string)($transferId ?? md5(time() + rand(1000, 2000))),
            'userid' => $this->userId,
            'currency' => $this->params->currency,
            'amount' => $amount,
            'i_actionid' => $game_number ?? 'D' . $this->getUniqueNumber(),
            'i_gameid' => $game_number ?? $this->getUniqueNumber(),
            'i_extparam' => '',
            'i_gamedesc' => '',
        ];
        $params['hmac'] = (new Hmac($params, $this->integration))->get();

        return $params;
    }

    public function win($amount, $game_number = null, $transferId = null)
    {
        $transfer_id = ($transferId) ? $transferId : md5(time() + rand(2000, 3000));
        $params = [
            'type' => 'credit',
            'tid' => ''.$transfer_id,
            'userid' => $this->userId,
            'currency' => $this->params->currency,
            'amount' => $amount,
            'i_actionid' => $game_number ?? 'C' . $this->getUniqueNumber(),
            'i_gameid' => $game_number ?? $this->getUniqueNumber(),
            'i_extparam' => '',
            'i_gamedesc' => '',
        ];
        $params['hmac'] = (new Hmac($params, $this->integration))->get();

        return $params;
    }

    public function renewHmac(array $params)
    {
        unset($params['hmac']);
        $params['hmac'] = (new Hmac($params, $this->integration))->get();

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

    private function basic($method)
    {
        $params = [
            'type' => $method,
        ];
        $params['hmac'] = (new Hmac($params, $this->integration))->get();

        return $params;
    }
}