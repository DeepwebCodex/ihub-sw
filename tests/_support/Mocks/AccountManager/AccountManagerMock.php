<?php

namespace Testing\AccountManager;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Testing\AccountManager\Protocol\ProtocolInterface;

/**
 * @deprecated
 *
 * Description of AccountManager
 *
 * @author petroff
 */
class AccountManagerMock extends BaseMock
{

    protected $protocol;
    protected $currentMock = AccountManager::class;
    protected $paramsCreateTransaction = [];
    protected $paramsCommitTransaction = [];
    protected $paramsGetUserInfo = [];
    protected $paramsGetFreeOperationId = [];
    protected $paramsGetFreeCardId = [];

    public function __construct(ProtocolInterface $protocol, $I = '')
    {
        parent::__construct($I);
        $this->protocol = $protocol;
        return $this;
    }

    function setParamsCreateTransaction($paramsCreateTransaction)
    {
        $this->paramsCreateTransaction = $paramsCreateTransaction;
        return $this;
    }

    function setParamsCommitTransaction($paramsCommitTransaction)
    {
        $this->paramsCommitTransaction = $paramsCommitTransaction;
        return $this;
    }

    function setParamsGetUserInfo($paramsGetUserInfo)
    {
        $this->paramsGetUserInfo = $paramsGetUserInfo;
        return $this;
    }

    function setParamsGetFreeOperationId($paramsGetFreeOperationId)
    {
        $this->paramsGetFreeOperationId = $paramsGetFreeOperationId;
        return $this;
    }

    function setParamsGetFreeCardId($paramsGetFreeCardId)
    {
        $this->paramsGetFreeCardId = $paramsGetFreeCardId;
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
    public function getMockAccountManager(array $paramsTransactions = [], array $userParams = [],
            int $freeOperationId = 0, int $freeCardId = 0)
    {
        if (!$paramsTransactions) {
            $paramsTransactions = $this->paramsCreateTransaction;
            if ($this->paramsCommitTransaction) {
                $paramsCommitTransactions = $this->paramsCommitTransaction;
            } else {
                $paramsCommitTransactions = $paramsTransactions;
            }
        } else {
            $paramsCommitTransactions = $paramsTransactions;
        }

        if (!$userParams) {
            $userParams = $this->paramsGetUserInfo;
        }

        if (!$freeOperationId && $this->paramsGetFreeOperationId) {
            $paramsGetFreeOperationId = $this->paramsGetFreeOperationId;
        } else {
            $paramsGetFreeOperationId = [
                'free_operation_id' => $freeOperationId
            ];
        }

        if (!$freeCardId && $this->paramsGetFreeCardId) {
            $paramsGetFreeCardId = $this->paramsGetFreeCardId;
        } else {
            $paramsGetFreeCardId = [
                'free_card_id' => $freeCardId
            ];
        }


        return $this->createTransaction($paramsTransactions)
                        ->commitTransaction($paramsCommitTransactions)
                        ->getUserInfo($userParams)
                        ->getFreeOperationId($paramsGetFreeOperationId)
                        ->getFreeCardId($paramsGetFreeCardId)
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
