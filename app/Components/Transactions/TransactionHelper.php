<?php

namespace App\Components\Transactions;


class TransactionHelper
{
    const DUPLICATE             = 'duplicate';
    const BAD_OPERATION_ORDER   = 'bad_operation_order';
    const INSUFFICIENT_FUNDS    = 'insufficient_funds';
    const ACCOUNT_DENIED        = 'account_denied';
    const UNKNOWN               = 'unknown';

    public static function getTransactionErrorState($errorCode)
    {
        switch ($errorCode){
            case 1402:
                return self::DUPLICATE;
            case 1403:
                return self::BAD_OPERATION_ORDER;
            case 1027:
                return self::INSUFFICIENT_FUNDS;
            case 1020:
            case -2:
                return self::ACCOUNT_DENIED;
            default:
                return self::UNKNOWN;
        }
    }

    public static function getTransactionErrorCode($code)
    {
        $codes = [
            self::DUPLICATE             => 1402,
            self::BAD_OPERATION_ORDER   => 1403,
            self::INSUFFICIENT_FUNDS    => 1027,
            self::ACCOUNT_DENIED        => 1020,
            self::ACCOUNT_DENIED        => -2,
        ];
        return $codes[$code] ?? null;
    }

    public static function amountCentsToWhole(int $amount){
        return $amount / 100;
    }
}