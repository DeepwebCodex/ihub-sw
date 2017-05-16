<?php

namespace Testing\DriveMedia;


use iHubGrid\Accounting\ExternalServices\AccountManager;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Testing\AccountManagerBaseMock;

class AccountManagerMock
{
    const BET = 1;
    const WIN = 0;

    public function __construct($params)
    {
        $this->params = $params;
        $this->mock = (new AccountManagerBaseMock($this->params))->getMock();
    }

    public function get()
    {
        return $this->mock;
    }

    public function userNotFound($wrongUserId)
    {
        $this->mock->shouldReceive('getUserInfo')->with($wrongUserId)->andThrow(new GenericApiHttpException(404, '', ['code' => 1024, 'message' => 'Account not found.']));

        return $this;
    }


    public function bet($object_id, $amount, $balance = null)
    {
        if(is_null($balance)){
            $balance = $this->params->balance;
        }

        $this->mock->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($object_id, $amount, self::BET))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET, $object_id,
                    $this->params->bet_operation_id, $balance - $amount));

        $this->mock->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams($object_id, self::BET, $this->params->bet_operation_id, $amount))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::BET, $object_id,
                    $this->params->bet_operation_id, $balance - $amount));

        return $this;
    }

    public function betExceeded($object_id, $amount)
    {
        $this->mock->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($object_id, $amount, self::BET))
            ->andThrow(new GenericApiHttpException(400, '{"code":1027,"message":""}', [], null, [], 1027));

        return $this;
    }

    public function win($object_id, $amount, $balance = null)
    {
        if(is_null($balance)){
            $balance = $this->params->balance;
        }
        $this->mock->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($object_id, $amount, self::WIN))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::WIN,
                    $object_id, $this->params->win_operation_id, $balance + $amount));

        $this->mock->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams($object_id, self::WIN, $this->params->win_operation_id, $amount))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::WIN, $object_id, $this->params->win_operation_id, $balance + $amount));

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
//        dump([
//            $status,
//            $this->params->serviceId,
//            $this->params->cashdeskId,
//            $this->params->userId,
//            $amount,
//            $this->params->currency,
//            $direction,
//            $object_id,
//            $this->getComment($object_id, $amount, $direction),
//            $this->params->partnerId,
//            $this->params->userIP,
//        ]);
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

    private function returnOk($status, $direction, $object_id, $operation_id, $balance)
    {
        return [
            "operation_id"          => $operation_id,
            "service_id"            => $this->params->serviceId,
            "cashdesk"              => $this->params->cashdeskId,
            "user_id"               => $this->params->userId,
            "partner_id"            => $this->params->partnerId,
            "move"                  => $direction,
            "status"                => $status,
            "object_id"             => $object_id,
            "currency"              => $this->params->currency,
            "deposit_rest"          => $balance,
        ];
    }

    private function getComment($object_id, $amount, $direction)
    {
//        dump(json_encode([
//            "comment" => ($direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $object_id,
//            "amount" => $amount,
//            "currency" => $this->params->currency
//        ]));

        return json_encode([
            "comment" => ($direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $object_id,
            "amount" => $amount,
            "currency" => $this->params->currency
        ]);
    }

    public function mock(\ApiTester $I)
    {
        if ($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $this->mock);
            $I->haveInstance(AccountManager::class, $this->mock);
        }
    }
}