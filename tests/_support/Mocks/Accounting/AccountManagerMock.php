<?php

namespace Testing\Accounting;


use iHubGrid\Accounting\ExternalServices\AccountManager;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Mockery;

class AccountManagerMock
{
    const BET = 1;
    const WIN = 0;

    private $bet_operation_id = 9543958;
    private $win_operation_id = 2834034;

    public function __construct(Params $params)
    {
        $this->params = $params;
        $this->mock = $this->getMock();
    }

    const SERVICE_IDS = [
        0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 14, 16, 17, 20, 21, 22, 23,
        24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 53, 54, 56, 107, 301
    ];

    private function getMock()
    {
        /** @var Mockery\Mock $accountManager */
        $accountManager = Mockery::mock(AccountManager::class);

        return $accountManager;
    }

    public function getFreeOperationId($free_operation_id)
    {
        $this->mock->shouldReceive('getFreeOperationId')->withNoArgs()->andReturn($free_operation_id);

        return $this;
    }

    public function userInfo($balance = null)
    {
        if(is_null($balance)){
            $balance = $this->params->getBalance();
        }

        $this->mock->shouldReceive('getUserInfo')
            ->withArgs([$this->params->userId])->andReturn(
                [
                    "id"            => $this->params->userId,
                    "wallets"       => [
                        [
                            "__record"  => "wallet",
                            "currency"  => $this->params->currency,
                            "is_active" => 1,
                            "deposit"   => $balance,
                        ],
                    ],
                    "user_services" => $this->getServices(),
                    "first_name"    => "Апаропао",
                    "last_name"     => "Паопаопаопао",
                ]
            );

        return $this;
    }

    private function getUniqueId()
    {
        return round(microtime(true)) + mt_rand(1, 10000);
    }

    private function getServices()
    {
        return array_map(function($service_id){
            return [
                "__record"              => "user_service",
                "service_id"            => $service_id,
                "is_enabled"            => 1,
            ];
        }, self::SERVICE_IDS);
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
            $balance = $this->params->getBalance();
        }

        $this->mock->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($object_id, $amount, self::BET))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET, $object_id,
                    $this->bet_operation_id, $amount, $balance));

        $this->mock->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams($object_id, self::BET, $this->bet_operation_id, $amount))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::BET, $object_id,
                    $this->bet_operation_id, $amount, $balance));

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
            $balance = $this->params->getBalance();
        }
        $this->mock->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($object_id, $amount, self::WIN))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::WIN,
                    $object_id, $this->win_operation_id, $amount, $balance));

        $this->mock->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams($object_id, self::WIN, $this->win_operation_id, $amount))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::WIN, $object_id, $this->win_operation_id, $amount, $balance));

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
//            $this->getComment($amount, $direction),
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

    private function returnOk($status, $direction, $object_id, $operation_id, $amount, $balance)
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
            "amount"                => $amount,
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

    public function mock(\ApiTester $I, $keepMock = true)
    {
        if ($this->params->enableMock) {
            $I->getApplication()->instance(AccountManager::class, $this->mock);
            if ($keepMock) {
                $I->haveInstance(AccountManager::class, $this->mock);
            }
        }
    }
}