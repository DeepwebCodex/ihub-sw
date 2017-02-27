<?php

namespace Testing\MicroGaming;

use App\Components\ExternalServices\AccountManager;
use App\Components\Integrations\MicroGaming\StatusCode;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

class AccountManagerMock
{
    private $object_id;
    private $no_bet_object_id;
    private $balance;
    const SERVICE_IDS = [
        0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 14, 16, 17, 20, 21, 22, 23,
        24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 53, 54, 107, 301
    ];

    const BET = 1;
    const WIN = 0;

    public function __construct()
    {
        $this->params = new Params();
        $this->object_id = $this->params->getPreparedObjectId(Params::OBJECT_ID);
        $this->duplicated_bet_object_id = $this->params->getPreparedObjectId(Params::DUPLICATED_BET_OBJECT_ID);
        $this->zero_bet_object_id = $this->params->getPreparedObjectId(Params::ZERO_BET_OBJECT_ID);
        $this->no_bet_object_id = Params::NO_BET_OBJECT_ID;

        $this->bet_operation_id = $this->getUniqueId();
        $this->duplicated_bet_operation_id = $this->getUniqueId();
        $this->win_operation_id = $this->getUniqueId();
        $this->zero_bet_operation_id = $this->getUniqueId();
        $this->zero_win_operation_id = $this->getUniqueId();

        $this->storage_pending_object_id = Params::STORAGE_PENDING_OBJECT_ID;
        $this->zero_win_object_id = Params::ZERO_WIN_OBJECT_ID;

        $this->amount = Params::AMOUNT;
        $this->currency = Params::CURRENCY;
        $this->balance = Params::BALANCE;
        $this->service_id = config('integrations.microgaming.service_id');
        $this->user_id = (int)env('TEST_USER_ID');
        $this->cashdesk = (int)env('TEST_CASHEDESK');
        $this->partner_id = (int)env('TEST_PARTNER_ID');
    }

    public function getMock()
    {
        /** @var Mockery\Mock $accountManager */
        $accountManager = Mockery::mock(AccountManager::class);

        $accountManager->shouldReceive('getUserInfo')
            ->withArgs([$this->user_id])->andReturn(
                [
                    "id"            => $this->user_id,
                    "wallets"       => [
                        [
                            "__record"  => "wallet",
                            "currency"  => $this->currency,
                            "is_active" => 1,
                            "deposit"   => $this->balance,
                        ],
                    ],
                    "user_services" => $this->getServices(),
                ]
            );

        $accountManager->shouldReceive('getFreeOperationId')->withNoArgs()->andReturn($this->getUniqueId());


        /** bet */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->amount, self::BET, $this->object_id, Params::OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET, $this->bet_operation_id, $this->balance));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::BET, $this->bet_operation_id, $this->object_id, $this->amount, Params::OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::BET, $this->bet_operation_id, $this->balance - $this->amount));


        /** win */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->amount, self::WIN, $this->object_id, Params::OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::WIN, $this->win_operation_id, $this->balance + $this->amount));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::WIN, $this->win_operation_id, $this->object_id, $this->amount, Params::OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::WIN, $this->win_operation_id, $this->balance + $this->amount));


        /** duplicated bet */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->amount, self::BET, $this->duplicated_bet_object_id, Params::DUPLICATED_BET_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET, $this->duplicated_bet_operation_id, $this->balance - $this->amount));


        /** zero win */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->amount, self::BET, $this->zero_bet_object_id, Params::ZERO_BET_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET, $this->zero_bet_operation_id, $this->balance));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::BET, $this->zero_bet_operation_id, $this->zero_bet_object_id, $this->amount, Params::ZERO_BET_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::BET, $this->zero_bet_operation_id, $this->balance - $this->amount));

        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams(0, self::WIN, $this->zero_win_object_id, Params::ZERO_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::WIN, $this->zero_win_operation_id, $this->balance + $this->amount));


        /** multi win */
        $multiWin_object_id = $this->params->getPreparedObjectId(Params::MULTI_WIN_OBJECT_ID);
        $multiWin_bet_operation_id = $this->getUniqueId();
        $win2_operation_id = $this->getUniqueId();

                    /** bet */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->amount, self::BET, $multiWin_object_id, Params::MULTI_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::BET, $multiWin_bet_operation_id, $this->balance));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::BET, $multiWin_bet_operation_id, $multiWin_object_id, $this->amount, Params::MULTI_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::BET, $multiWin_bet_operation_id, $this->balance - $this->amount));

                    /** win1 */

        $win1_operation_id = $this->getUniqueId();
        $win1_amount = Params::AMOUNT;

        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($win1_amount, self::WIN, $multiWin_object_id, Params::MULTI_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::WIN, $win1_operation_id, $this->balance));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::WIN, $win1_operation_id, $multiWin_object_id, $win1_amount, Params::MULTI_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::WIN, $win1_operation_id, $this->balance + $win1_amount));

                    /** win2 */
        $jackpot_amount = Params::JACKPOT_AMOUNT;
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($jackpot_amount, self::WIN, $multiWin_object_id, Params::MULTI_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_PENDING, self::WIN, $win2_operation_id, $this->balance));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::WIN, $win2_operation_id, $multiWin_object_id, $jackpot_amount, Params::MULTI_WIN_OBJECT_ID))
            ->andReturn(
                $this->returnOk(TransactionRequest::STATUS_COMPLETED, self::WIN, $win2_operation_id, $this->balance + $win1_amount + $jackpot_amount));

        /** no bet win */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                [
                    TransactionRequest::STATUS_COMPLETED,
                    $this->service_id,
                    $this->cashdesk,
                    $this->user_id,
                    $this->amount,
                    $this->currency,
                    self::WIN,
                    $this->no_bet_object_id,
                    $this->getComment($this->amount, self::WIN, $this->no_bet_object_id),
                    $this->partner_id
                ])
            ->andThrow(new ApiHttpException(
                Response::HTTP_BAD_REQUEST,
                '', [], null, [],
                TransactionHelper::BAD_OPERATION_ORDER_CODE));

        /** storage pending */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                [
                    TransactionRequest::STATUS_COMPLETED,
                    $this->service_id,
                    $this->cashdesk,
                    $this->user_id,
                    $this->amount,
                    $this->currency,
                    self::BET,
                    $this->storage_pending_object_id,
                    $this->getComment($this->amount, self::BET, $this->storage_pending_object_id),
                    $this->partner_id
                ])
            ->andReturn($this->returnOk('completed', self::BET, $this->storage_pending_object_id, $this->balance - $this->amount));


        return $accountManager;
    }

    private function getUniqueId()
    {
        return round(microtime(true)) + mt_rand(1, 10000);
    }


    private function getPendingParams($amount, $direction, $object_id, $comment_object_id)
    {
        return [
            TransactionRequest::STATUS_PENDING,
            $this->service_id,
            $this->cashdesk,
            $this->user_id,
            $amount,
            $this->currency,
            $direction,
            $object_id,
            $this->getComment($amount, $direction, $comment_object_id),
            $this->partner_id,
        ];
    }

    private function getCompletedParams($direction, $operation_id, $object_id, $amount, $comment_object_id)
    {
        return [
            $this->user_id,
            $operation_id,
            $direction,
            $object_id,
            $this->currency,
            $this->getComment($amount, $direction, $comment_object_id),
        ];
    }

    private function returnOk($status, $direction, $operation_id, $balance)
    {
        return [
            "operation_id"          => $operation_id,
            "service_id"            => $this->service_id,
            "cashdesk"              => $this->cashdesk,
            "user_id"               => $this->user_id,
            "partner_id"            => $this->partner_id,
            "move"                  => $direction,
            "status"                => $status,
            "object_id"             => $this->object_id,
            "currency"              => $this->currency,
            "deposit_rest"          => $balance,
        ];
    }

    private function getComment($amount, $direction, $object_id)
    {
        return json_encode([
            "comment" => ($direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $object_id,
            "amount" => $amount,
            "currency" => 'EUR'
        ]);
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
}