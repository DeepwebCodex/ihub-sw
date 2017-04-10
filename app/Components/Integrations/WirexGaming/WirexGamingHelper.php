<?php

namespace App\Components\Integrations\WirexGaming;

use App\Components\Transactions\Strategies\MicroGaming\ProcessWirexGaming;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;

/**
 * Class WirexGamingHelper
 * @package App\Components\Integrations\WirexGaming
 */
class WirexGamingHelper
{
    /**
     * @param $oid
     * @return int
     */
    public static function makeUid($oid)
    {
        $pid = config('integrations.wirexGaming.pid]');
        return ($oid << 16) + $pid;
    }

    /**
     * @param $uid
     * @return int
     */
    public static function parseUid($uid)
    {
        return $uid >> 16;
    }

    /**
     * @param string $currency
     * @return mixed
     */
    public static function mapCurrencyCode(string $currency)
    {
        $currencyMap = config('integrations.microgaming.list_currency');

        return array_get($currencyMap, $currency, $currency);
    }

    /**
     * @param $userCurrency
     */
    public static function checkSessionCurrency($userCurrency)
    {
        if ($userCurrency != \app('GameSession')->get('currency')) {
            throw new ApiHttpException(
                409,
                'Currency mismatch',
                CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY)
            );
        }
    }

    /**
     * @param TransactionRequest $transactionRequest
     * @param $user
     * @return \App\Components\Transactions\TransactionResponse
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public static function handleTransaction($transactionRequest, $user)
    {
        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessWirexGaming());

        if ($transactionResponse->isDuplicate()) {
            throw new ApiHttpException(
                409,
                null,
                \array_merge(CodeMapping::getByMeaning(CodeMapping::DUPLICATE))
            );
        }
        if ($transactionResponse->operation_id === null) {
            throw new ApiHttpException(504, null, CodeMapping::getByMeaning(CodeMapping::TIMED_OUT));
        }
        return $transactionResponse;
    }
}
