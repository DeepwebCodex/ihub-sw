<?php

namespace NetEntertainment;

use App\Components\Integrations\Fundist\Hmac;
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

    public function __construct(string $integration)
    {
        $this->userId = (int)env('TEST_USER_ID') . '_' . Params::CURRENCY;
        $this->currency = Params::CURRENCY;
        $this->amount_backup =
        $this->amount = Params::AMOUNT;
        $this->bigAmount = Params::BIG_AMOUNT;
        $this->game_id = Params::GAME_ID;
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
            'currency' => $this->currency,
        ];
        $params['hmac'] = (new Hmac($params, $this->integration))->get();

        return $params;
    }

    public function bet($amount = 1, $game_number = null, $transferId = null)
    {
        $transfer_id = ($transferId) ? $transferId : time() + random_int(1000, 2000);
        $params = [
            'type' => 'debit',
            'tid' => '' . $transfer_id,
            'userid' => $this->userId,
            'currency' => $this->currency,
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
        $transfer_id = ($transferId) ? $transferId : time() + random_int(2000, 3000);
        $params = [
            'type' => 'credit',
            'tid' => '' . $transfer_id,
            'userid' => $this->userId,
            'currency' => $this->currency,
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

    public function getUniqueNumber()
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
        return $this->amount = $this->amount_backup;
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
