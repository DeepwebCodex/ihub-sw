<?php

namespace Endorphina;

use App\Components\Integrations\Endorphina\Sign;
use App\Components\Users\IntegrationUser;
use Helper\TestUser;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Testing\AccountManager\AccountManagerMock;
use Testing\AccountManager\Protocol\ProtocolInterface;
use Testing\AccountManager\Protocol\v1\DefaultParams;
use function config;
use function env;

class TestData
{

    const AMOUNT = 10;

    /**
     * @var IntegrationUser
     */
    public $userId;
    public $currency = 'EUR';
    public $user;
    private $amount = 13 * 100;
    private $game = 'endorphina_Geisha@ENDORPHINA';
    private $gameId = 1;
    private $protocol;
    private $I;
    private $serviceId;

    public function __construct(ProtocolInterface $protocol)
    {

        $this->userId = (int) env('TEST_USER_ID');
        $this->partnerId = (int) env('TEST_PARTNER_ID');
        $this->cashdeskId = (int) env('TEST_CASHEDESK');
        $this->protocol = $protocol;
        $this->serviceId = config('integrations.endorphina.service_id');
    }

    private function setSignature(array $data)
    {
        return Sign::generate($data);
    }

    private function getToken()
    {
        return md5($this->userId);
    }

    private function rand()
    {
        return time() + mt_rand(1, 10000);
    }

    public function setI($I)
    {
        $this->I = $I;
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
        $userCurrency = DefaultParams::CURRENCY;
        $data = [
            'currency' => $userCurrency,
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
        ];
        $accoutManagerMock = new AccountManagerMock($this->protocol, $this->I);
        $accoutManagerMock->getUserInfo([])->getMock();
        $this->user = new TestUser();

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketBet(int $betId = 0, $userAmount = null, $depositRest = 0)
    {

        $userBalance = DefaultParams::AMOUNT_BALANCE;
        $userCurrency = DefaultParams::CURRENCY;
        if (!$betId) {
            $betId = $this->rand();
        }

        if ($depositRest === 0) {
            $depositRest = $userBalance - ($this->amount / 100);
        }


        $data = [
            'currency' => $userCurrency,
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $betId,
            'amount' => $this->amount
        ];
        $paramsTransactions = [
            'object_id' => $this->rand(),
            'operation_id' => $this->rand(),
            'service_id' => config('integrations.endorphina.service_id'),
            'deposit_rest' => $depositRest,
            'amount' => ($this->amount / 100)
        ];
        if ($userAmount !== null) {
            $userParams = ['balance' => $userAmount];
        } else {
            $userParams = [];
        }
        $accoutManagerMock = new AccountManagerMock($this->protocol, $this->I);
        $accoutManagerMock->setParamsCreateTransaction($paramsTransactions)
                ->setParamsGetUserInfo($userParams)
                ->getMockAccountManager($paramsTransactions, $userParams);
        $this->user = new TestUser();

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketWin($amount = null)
    {
        $userBalance = DefaultParams::AMOUNT_BALANCE;
        $userCurrency = DefaultParams::CURRENCY;
        if ($amount === null) {
            $amount = $this->amount;
        }
        $data = [
            'currency' => $userCurrency,
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $this->rand(),
            'amount' => $amount
        ];
        $paramsTransactions = [
            'object_id' => $this->rand(),
            'operation_id' => $this->rand(),
            'service_id' => config('integrations.endorphina.service_id'),
            'deposit_rest' => $userBalance + ($this->amount / 100),
            'amount' => ($amount / 100),
            'move' => 0
        ];
        $accoutManagerMock = new AccountManagerMock($this->protocol, $this->I);
        $accoutManagerMock->getMockAccountManager($paramsTransactions);
        $this->user = new TestUser();

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketRefund(int $betId)
    {
        $userBalance = DefaultParams::AMOUNT_BALANCE;
        $userCurrency = DefaultParams::CURRENCY;

        $data = [
            'currency' => $userCurrency,
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $betId,
            'amount' => $this->amount
        ];
        $paramsTransactions = [
            'object_id' => $this->rand(),
            'operation_id' => $this->rand(),
            'service_id' => config('integrations.endorphina.service_id'),
            'deposit_rest' => $userBalance + ($this->amount / 100),
            'amount' => ($this->amount / 100),
            'move' => 0
        ];
        $accoutManagerMock = new AccountManagerMock($this->protocol, $this->I);
        $accoutManagerMock->getMockAccountManager($paramsTransactions);
        $this->user = new TestUser();

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketRefundWithoutBet()
    {
        $userBalance = DefaultParams::AMOUNT_BALANCE;
        $userCurrency = DefaultParams::CURRENCY;

        $data = [
            'currency' => $userCurrency,
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $this->rand(),
            'amount' => $this->amount
        ];
        $paramsTransactions = [
            'object_id' => $this->rand(),
            'operation_id' => $this->rand(),
            'service_id' => config('integrations.endorphina.service_id'),
            'deposit_rest' => $userBalance,
            'amount' => $this->amount,
            'move' => 0
        ];
        $accoutManagerMock = new AccountManagerMock($this->protocol, $this->I);
        $accoutManagerMock->getMockAccountManager($paramsTransactions);
        $this->user = new TestUser();

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getWrongPacketBet()
    {

 
        $data = [
            'currency' => 'EUR',
            'player' => (string) $this->userId,
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
