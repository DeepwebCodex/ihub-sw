<?php

namespace App\Components\Integrations\WirexGaming;

use App\Components\Transactions\Strategies\MicroGaming\ProcessWirexGaming;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;

/**
 * Class WirexGamingHelper
 * @package App\Components\Integrations\WirexGaming
 */
class WirexGamingHelper
{
    /**
     * @param $serverPid
     * @return mixed
     */
    protected static function getConfigPid($serverPid)
    {
        $partnersConfig = config('integrations.wirexGaming.partners_config');
        if ($partnersConfig) {
            $partnersConfig = collect($partnersConfig);
            $partnerConfig = $partnersConfig->where('server_pid', '=', $serverPid)->first();
            return $partnerConfig['previous_context_id'];
        }
        return config('integrations.wirexGaming.previous_context_id');
    }

    /**
     * @param $serverPid
     * @param $uid
     * @return int
     */
    public static function parseUid($serverPid, $uid)
    {
        $previousContextId = self::getConfigPid($serverPid);
        if (empty($previousContextId)) {
            throw new ApiHttpException(
                409,
                'Config error',
                CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR)
            );
        }

        $currencyBitShift = 52;
        $oidBitShift = 16;
        // currency code added to pass wirex different user_uid for different currencies
        $currencyCode = $uid >> $currencyBitShift;
        $currencyBinVal = $currencyCode << $currencyBitShift;

        return ($uid - $currencyBinVal - $previousContextId) >> $oidBitShift;
    }

    /**
     * @param string $userCurrency
     * @param string $requestCurrency
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    public static function checkRequestCurrency(string $userCurrency, string $requestCurrency)
    {
        if ($userCurrency !== $requestCurrency) {
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
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
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
            throw new ApiHttpException(
                504,
                null,
                CodeMapping::getByMeaning(CodeMapping::TIMED_OUT)
            );
        }
        return $transactionResponse;
    }
}
