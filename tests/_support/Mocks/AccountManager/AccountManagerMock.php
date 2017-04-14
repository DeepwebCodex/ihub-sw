<?php

namespace Testing\AccountManager;

use App\Components\ExternalServices\AccountManager;
use Testing\AccountManager\Protocol\ProtocolInterface;
use Testing\AccountManager\Protocol\v1\DefaultParams;
use Testing\BaseMock;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

    public function getMockAccountManager(array $paramsTransactions, array $userParams = [], int $freeOperationId = 0, int $freeCardId = 0)
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
