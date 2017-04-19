<?php

namespace Testing\AccountManager;

use App\Components\ExternalServices\AccountManager;
use Testing\AccountManager\Protocol\ProtocolInterface;
use Testing\BaseMock;

/**
 * Description of AccountManager
 *
 * @author petroff
 */
class AccountManagerMock extends BaseMock
{

    protected $protocol;
    protected $currentMock = AccountManager::class;

    function __construct(ProtocolInterface $protocol, $I = '')
    {
        parent::__construct($I);
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * Example
     * $paramsTransactions = [
     * 'object_id' => $request['params']['bet_id'],
     * 'operation_id' => $this->getUniqInt(),
     * 'service_id' => 13,
     * 'deposit_rest' => DefaultParams::AMOUNT_BALANCE - ($this->data->getAmount() / 100),
     * 'amount' => ($this->data->getAmount() / 100) * -1
     * ];
     */
    public function getMockAccountManager(array $paramsTransactions, array $userParams = [], int $freeOperationId = 0,
            int $freeCardId = 0)
    {

        return $this->createTransaction($paramsTransactions)
                        ->commitTransaction($paramsTransactions)
                        ->getUserInfo($userParams)
                        ->getFreeOperationId([
                            'free_operation_id' => $freeOperationId
                        ])
                        ->getFreeCardId([
                            'free_card_id' => $freeCardId
                        ])
                        ->getMock();
    }

    public function createTransaction(array $params)
    {
        $this->currentMock = parent::createMock($this->currentMock, __FUNCTION__, $this->protocol->getResponse(__FUNCTION__)->getProtocol($params));
        return $this;
    }

    public function commitTransaction(array $params)
    {
        $this->currentMock = parent::createMock($this->currentMock, __FUNCTION__, $this->protocol->getResponse(__FUNCTION__)->getProtocol($params));
        return $this;
    }

    public function getUserInfo(array $params)
    {
        $this->currentMock = parent::createMock($this->currentMock, __FUNCTION__, $this->protocol->getResponse(__FUNCTION__)->getProtocol($params));
        return $this;
    }

    public function getFreeOperationId(array $params)
    {
        $this->currentMock = parent::createMock($this->currentMock, __FUNCTION__, $this->protocol->getResponse(__FUNCTION__)->getProtocol($params));
        return $this;
    }

    public function getFreeCardId(array $params)
    {
        $this->currentMock = parent::createMock($this->currentMock, __FUNCTION__, $this->protocol->getResponse(__FUNCTION__)->getProtocol($params));
        return $this;
    }

    public function getMock()
    {
        return parent::injectMock($this->currentMock);
    }

}
