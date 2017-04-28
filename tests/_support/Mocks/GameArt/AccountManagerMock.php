<?php

namespace Testing\GameArt;
//namespace tests\_support\Mocks\GameArt;

use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Testing\AccountManagerBaseMock;

class AccountManagerMock
{
    const BET = 1;
    const WIN = 0;

    public function __construct()
    {
        $this->params = new Params();
        $this->mock = (new AccountManagerBaseMock($this->params))->getMock();
    }

    public function get()
    {
        return $this->mock;
    }

    public function userNotFound()
    {
        $this->mock->shouldReceive('getUserInfo')->with($this->params->wrongUserId)->andThrow(new GenericApiHttpException(500, 'Account not found.'));

        return $this;
    }


    public function bet()
    {
        $this->mock->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->params->object_id, $this->params->amount, self::BET))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET,
                    $this->params->bet_operation_id, $this->params->balance - $this->params->amount));

        $this->mock->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams($this->params->object_id, self::BET, $this->params->bet_operation_id, $this->params->amount))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::BET, $this->params->bet_operation_id, $this->params->balance - $this->params->amount));

        return $this;
    }

    private function getCompletedParams($object_id, $direction, $operation_id, $amount)
    {
        return [
            $this->params->userId,
            $operation_id,
            $direction,
            $object_id,
            $this->params->currency,
            $this->getComment($object_id, $amount, $direction),
            $this->params->userIP,
        ];
    }

    /**
     * status, service_id, cashdesk, user_id, amount,
     * currency, direction, object_id, comment, partner_id
     */
    private function getPendingParams($object_id, $amount, $direction, $status = TransactionRequest::STATUS_PENDING)
    {
        return [
            $status,
            $this->params->serviceId,
            $this->params->cashdeskId,
            $this->params->userId,
            $amount,
            $this->params->currency,
            $direction,
            $object_id,
            $this->getComment($object_id, $amount, $direction),
            $this->params->partnerId,
            $this->params->userIP,
        ];
    }

    private function returnOk($status, $direction, $operation_id, $balance)
    {
        return [
            "operation_id"          => $operation_id,
            "service_id"            => $this->params->serviceId,
            "cashdesk"              => $this->params->cashdeskId,
            "user_id"               => $this->params->userId,
            "partner_id"            => $this->params->partnerId,
            "move"                  => $direction,
            "status"                => $status,
            "object_id"             => $this->params->object_id,
            "currency"              => $this->params->currency,
            "deposit_rest"          => $balance,
        ];
    }

    private function getComment($object_id, $amount, $direction)
    {
        return json_encode([
            "comment" => ($direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $object_id,
            "amount" => $amount,
            "currency" => $this->params->currency
        ]);
    }
}