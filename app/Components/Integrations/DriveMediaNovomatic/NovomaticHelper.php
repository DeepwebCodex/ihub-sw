<?php

namespace App\Components\Integrations\DriveMediaNovomatic;

use App\Components\Transactions\Strategies\DriveMedia\ProcessNovomatic;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionResponse;
use App\Exceptions\Api\ApiHttpException;

/**
 * Class NovomaticHelper
 * @package App\Components\Integrations\Novomatic
 */
class NovomaticHelper
{
    /**
     * @param string $playType
     * @return mixed
     */
    public static function getTransactionType(string $playType)
    {
        $map = [
            'bet' => TransactionRequest::TRANS_BET,
            'win' => TransactionRequest::TRANS_WIN,
        ];
        return array_get($map, $playType);
    }

    /**
     * @param string $playType
     * @return mixed
     */
    public static function getTransactionDirection(string $playType)
    {
        $map = [
            'bet' => TransactionRequest::D_WITHDRAWAL,
            'win' => TransactionRequest::D_DEPOSIT,
        ];
        return array_get($map, $playType);
    }

    /**
     * @param float $bet
     * @param float $winLose
     * @return array
     */
    public static function getTransactions(float $bet, float $winLose): array
    {
        if ($bet == 0) {
            return [
                self::makeTransactionItem($winLose, TransactionRequest::TRANS_WIN)
            ];
        }

        if ($winLose >= 0) {
            return [
                self::makeTransactionItem($bet, TransactionRequest::TRANS_BET),
                self::makeTransactionItem($winLose, TransactionRequest::TRANS_WIN)
            ];
        }

        if ($bet == ($winLose * -1)) {
            return [
                self::makeTransactionItem($bet, TransactionRequest::TRANS_BET)
            ];
        }

        return [
            self::makeTransactionItem($bet, TransactionRequest::TRANS_BET),
            self::makeTransactionItem($winLose + $bet, TransactionRequest::TRANS_WIN)
        ];
    }

    /**
     * @param $amount
     * @param $type
     * @return array
     */
    protected static function makeTransactionItem($amount, $type)
    {
        return [
            'amount' => $amount,
            'type' => $type
        ];
    }

    /**
     * @param TransactionRequest $transactionRequest
     * @param IntegrationUser $user
     * @return TransactionResponse
     */
    public static function handleTransaction($transactionRequest, $user)
    {
        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        return $transactionHandler->handle(new ProcessNovomatic());
    }

    /**
     * @param $space
     * @return mixed
     */
    public static function getKey($space)
    {
        $spaces = config("integrations.DriveMediaNovomatic.spaces");

        foreach ($spaces as $v) {
            if($v['id'] === $space) {
                return $v['key'];
            }
        }

        throw new ApiHttpException(200, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @param $space
     * @return mixed
     */
    public static function getSpace($space)
    {
        $spaces = config("integrations.DriveMediaNovomatic.spaces");

        foreach ($spaces as $v) {
            if($v['id'] === $space) {
                return true;
            }
        }

        return false;
    }
}
