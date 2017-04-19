<?php

namespace Endorphina;

use App\Components\Integrations\Endorphina\Sign;
use App\Components\Users\IntegrationUser;
use Helper\TestUser;
use Testing\AccountManager\Protocol\ProtocolV1;
use Testing\MicroGaming\AccountManagerMock;
use function env;

class TestData
{

    const AMOUNT = 10;

    /**
     * @var IntegrationUser
     */
    public $userId;
    public $currency = 'EUR';
    private $amount = 13 * 100;
    private $user;
    private $game = 'endorphina_Geisha@ENDORPHINA';
    private $gameId = 1;
    private $protocol;
    private $I;

    public function __construct(TestUser $user, ProtocolInterface $protocol)
    {
        $this->userId = (int) env('TEST_USER_ID');
        $this->partnerId = (int) env('TEST_PARTNER_ID');
        $this->cashdeskId = (int) env('TEST_CASHEDESK');
        $this->user = $user;
        $this->protocol = $protocol;
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
        $data = [
            'currency' => $this->user->getCurrency(),
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
        ];

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

    public function getPacketBet()
    {

        $data = [
            'currency' => $this->user->getCurrency(),
            'player' => (string) $this->userId,
            'game' => $this->game,
            'token' => $this->getToken(),
            'gameId' => $this->gameId,
            'date' => time(),
            'id' => $this->rand(),
            'amount' => $this->amount
        ];

        $accoutManagerMock = new AccountManagerMock($this->protocol, $this->I);
        $accoutManagerMock->getMockAccountManager($data);

        $data['sign'] = $this->setSignature($data);
        return $data;
    }

}
