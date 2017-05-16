<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/6/16
 * Time: 3:46 PM
 */

namespace Testing;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHelper;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
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
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 53, 54, 56, 107, 301
    ];

    const BET = 1;
    const WIN = 0;

    public function __construct(
        $serviceId,
        $amount = Params::AMOUNT,
        $currency = Params::CURRENCY
    )
    {
        $this->object_id = Params::OBJECT_ID;
        $this->no_bet_object_id = Params::NO_BET_OBJECT_ID;

        $this->bet_operation_id = $this->getUniqueId();
        $this->win_operation_id = $this->getUniqueId();
        $this->jackpot_operation_id = $this->getUniqueId();

        $this->amount = $amount;
        $this->jackpot_amount = Params::JACKPOT_AMOUNT;
        $this->winAmount = Params::WIN_AMOUNT;
        $this->currency = $currency;
        $this->balance = Params::BALANCE;
        $this->service_id = $serviceId;
        $this->user_id = (int)env('TEST_USER_ID');
        $this->cashdesk = (int)env('TEST_CASHEDESK');
        $this->partner_id = (int)env('TEST_PARTNER_ID');
        $this->client_ip = "127.0.0.1";
    }

    public function getMock()
    {
        /** @var Mockery\Mock $accountManager */
        $accountManager = Mockery::mock(AccountManager::class);

        $accountManager->shouldReceive('getUserInfo')
            ->withArgs([$this->user_id])->andReturn(
            [
                "__record"              => "account",
                "cluster_id"            => null,
                "id"                    => $this->user_id,
                "group"                 => 2,
                "login"                 => "ziwidif@rootfest.net",
                "email"                 => "ziwidif@rootfest.net",
                "sess"                  => "dp0i630iih0auinqsbmqtrfk27",
                "password"              => "6ba5b1d8065b3560f0e29789213ff54e",
                "hash_type"             => 1,
                "password_hach_old"     => null,
                "last_ip"               => "10.1.4.51",
                "status_id"             => 2,
                "first_name"            => "Апаропао",
                "middle_name"           => "Апоапопаоапо",
                "last_name"             => "Паопаопаопао",
                "lang"                  => "ru",
                "timezone"              => "Europe/Kiev",
                "tzoffset"              => 7200.0,
                "phone_number"          => "380501072339",
                "date_of_birth"         => "1991-06-06 00:00:00",
                "country_id"            => "UA",
                "city"                  => null,
                "zip"                   => null,
                "adress"                => null,
                "question"              => "Mother's maiden name?",
                "answer"                => "filler",
                "registration_date"     => "2016-10-21 07:35:48",
                "title"                 => "mr",
                "documents"             => null,
                "cashdesk"              => $this->cashdesk,
                "deleted"               => 0,
                "wallets"               => [
                    [
                        "__record"                         => "wallet",
                        "user_id"                          => $this->user_id,
                        "payment_instrument_id"            => 3,
                        "payment_instrument_name"          => "Skrill",
                        "wallet_id"                        => "ziwidif@rootfest.net",
                        "wallet_account_id"                => $this->currency,
                        "partner_id"                       => 1,
                        "currency"                         => $this->currency,
                        "is_default"                       => 0,
                        "is_active"                        => 1,
                        "deposit"                          => $this->balance,
                        "creation_date"                    => "2016-10-21 13:37:14",
                        "payment_instrument_transfer_time" => "00.00",
                        "cashdesk"                         => $this->cashdesk,
                        "deleted"                          => 0,
                    ],
                    [
                        "__record"                         => "wallet",
                        "user_id"                          => $this->user_id,
                        "payment_instrument_id"            => 5,
                        "payment_instrument_name"          => "Bonuses",
                        "wallet_id"                        => "3000053",
                        "wallet_account_id"                => "BNS",
                        "partner_id"                       => 1,
                        "currency"                         => "BNS",
                        "is_default"                       => 0,
                        "is_active"                        => 0,
                        "deposit"                          => 0.0,
                        "creation_date"                    => "2016-10-21 07:35:48",
                        "payment_instrument_transfer_time" => "00.00",
                        "cashdesk"                         => $this->cashdesk,
                        "deleted"                          => 0,
                    ],
                ],
                "user_services"         => $this->getServices(),
                "trust_level"           => 100,
                "blacklist"             => 0,
                "loyalty_rating"        => 0,
                "loyalty_points"        => 0,
                "loyalty_months"        => 0,
                "loyalty_deposit_count" => 0,
                "loyalty_rating_level"  => 0,
                "fav_bet_club_user"     => 1,
                "coupon"                => null,
                "mobile_is_active"      => 0,
                "email_is_active"       => 1,
                "spam_ok"               => 1,
                "partner_id"            => 1,
                "data"                  => null,
                "token"                 => "89",
                "oib"                   => null,
                "nationality"           => null,
                "region"                => null,
                "fullname"              => "Апаропао Апоапопаоапо Паопаопаопао",
            ]
        );

        $bigBetPendingParams = $this->getPendingParams(Params::BIG_AMOUNT, self::BET);

        /** bet */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->amount, self::BET))
            ->andReturn(
                $this->returnPending(self::BET, $this->amount, $this->bet_operation_id));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::BET, $this->bet_operation_id, $this->amount))
            ->andReturn(
                $this->returnCompleted(self::BET, $this->amount, $this->balance - $this->amount));

        /** big bet */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs($bigBetPendingParams)
            ->andThrow(new GenericApiHttpException(
                Response::HTTP_BAD_REQUEST,
                '', [], null, [],
                TransactionHelper::INSUFFICIENT_FUNDS_CODE));

        /** win */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs($this->getPendingParams($this->amount, self::WIN))
            ->andReturn($this->returnPending(self::WIN, $this->amount, $this->win_operation_id));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::WIN, $this->win_operation_id, $this->amount))
            ->andReturn(
                $this->returnCompleted(self::WIN, $this->amount, $this->balance + $this->amount));


        /** bet and win */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->winAmount, self::WIN))
            ->andReturn(
                $this->returnPending(self::WIN, $this->balance - $this->amount + $this->winAmount, $this->win_operation_id));

        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::WIN, $this->win_operation_id, $this->winAmount))
            ->andReturn(
                $this->returnCompleted(self::WIN, $this->winAmount, $this->balance - $this->amount + $this->winAmount));

        /** multi win */
        $accountManager->shouldReceive('createTransaction')
            ->withArgs(
                $this->getPendingParams($this->jackpot_amount, self::WIN))
            ->andReturn(
                $this->returnPending(self::WIN, $this->balance - $this->amount + $this->winAmount + $this->jackpot_amount, $this->jackpot_operation_id));
        $accountManager->shouldReceive('commitTransaction')
            ->withArgs(
                $this->getCompletedParams(self::WIN, $this->jackpot_operation_id, $this->jackpot_amount))
            ->andReturn(
                $this->returnCompleted(self::WIN, $this->jackpot_amount, $this->balance - $this->amount + $this->winAmount + $this->jackpot_amount));

        $accountManager->shouldReceive('getFreeOperationId')->withNoArgs()->andReturn($this->getUniqueId());
        $accountManager->shouldReceive('getOperations')->withAnyArgs()->andReturn(null);


        return $accountManager;
    }

    private function getUniqueId()
    {
        return round(microtime(true)) + mt_rand(1, 10000);
    }

    private function getCompletedParams($direction, $operation_id, $amount)
    {
        return [
            $this->user_id,
            $operation_id,
            $direction,
            $this->object_id,
            $this->currency,
            $this->getComment($amount, $direction),
            $this->client_ip
        ];
    }

    /**
     * status, service_id, cashdesk, user_id, amount,
     * currency, direction, object_id, comment, partner_id
     */
    private function getPendingParams($amount, $direction, $status = TransactionRequest::STATUS_PENDING)
    {
        return [
            $status,
            $this->service_id,
            $this->cashdesk,
            $this->user_id,
            $amount,
            $this->currency,
            $direction,
            $this->object_id,
            $this->getComment($amount, $direction),
            $this->partner_id,
            $this->client_ip
        ];
    }

    private function returnPending($direction, $amount, $operation_id)
    {
        return [
            "__record"              => "operation",
            "operation_id"          => $operation_id,
            "service_id"            => $this->service_id,
            "cashdesk"              => $this->cashdesk,
            "user_id"               => $this->user_id,
            "payment_instrument_id" => 3,
            "wallet_id"             => "ziwidif@rootfest.net",
            "wallet_account_id"     => $this->currency,
            "partner_id"            => $this->partner_id,
            "move"                  => $direction,
            "status"                => "pending",
            "dt"                    => "2017-02-15 10:08:03",
            "dt_done"               => null,
            "object_id"             => $this->object_id,
            "amount"                => -$amount,
            "currency"              => $this->currency,
            "client_ip"             => "127.0.0.1",
            "comment"               => $this->getComment($amount, $direction),
            "deposit_rest"          => $this->balance,
        ];
    }

    private function returnCompleted($direction, $amount, $balance)
    {
        $operation_id = ($direction) ? $this->bet_operation_id : $this->win_operation_id;
        return [
            "__record"              => "operation",
            "operation_id"          => $operation_id,
            "service_id"            => $this->service_id,
            "cashdesk"              => $this->cashdesk,
            "user_id"               => $this->user_id,
            "payment_instrument_id" => 3,
            "wallet_id"             => "ziwidif@rootfest.net",
            "wallet_account_id"     => $this->currency,
            "partner_id"            => $this->partner_id,
            "move"                  => $direction,
            "status"                => "completed",
            "dt"                    => "2017-02-15 10:08:03",
            "dt_done"               => null,
            "object_id"             => $this->object_id,
            "amount"                => -$amount,
            "currency"              => $this->currency,
            "client_ip"             => "127.0.0.1",
            "comment"               => $this->getComment($amount, $direction),
            "deposit_rest"          => $balance,
        ];
    }

    private function getComment($amount, $direction)
    {
        return json_encode([
            "comment" => ($direction ? 'Withdrawal' : 'Deposit') . ' for object_id: ' . $this->object_id,
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