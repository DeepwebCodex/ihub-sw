<?php

namespace Endorphina;

use App\Components\Integrations\Endorphina\Sign;
use Testing\Accounting\Params;

class TestData
{
    private $amount = 13 * 100;
    private $game = 'endorphina_Geisha@ENDORPHINA';
    private $gameId = 1;

    public function __construct(Params $params)
    {
        $this->params = $params;
    }

    private function setSignature(array $data)
    {
        return Sign::generate($data);
    }

    private function getToken()
    {
        return md5($this->params->userId);
    }

    private function rand()
    {
        return time() + mt_rand(1, 10000);
    }

    public function getPacketSession()
    {
        $data = [
            'token' => $this->getToken(),
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketBalance()
    {
        $data = [
            'currency' => $this->params->currency,
            'player' => (string) $this->params->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketBet($amount = 1300, int $betId = null)
    {
        $betId = $betId ?? $this->rand();

        $data = [
            'currency' => $this->params->currency,
            'player' => (string) $this->params->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $betId,
            'amount' => $amount
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketWin($amount)
    {
        $data = [
            'currency' => $this->params->currency,
            'player' => (string) $this->params->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $this->rand(),
            'amount' => $amount
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketRefund(int $betId, $amount)
    {
        $data = [
            'currency' => $this->params->currency,
            'player' => (string) $this->params->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $betId,
            'amount' => $amount
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketRefundWithoutBet()
    {
        $data = [
            'currency' => $this->params->currency,
            'player' => (string) $this->params->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $this->rand(),
            'amount' => $this->amount
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getWrongPacketBet()
    {
        $data = [
            'currency' => 'EUR',
            'player' => (string) $this->params->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'amount' => $this->amount
        ];
       
        $data['sign'] = $this->setSignature($data);
        return $data;
    }

}
