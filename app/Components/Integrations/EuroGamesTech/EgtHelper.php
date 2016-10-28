<?php

namespace App\Components\Integrations\EuroGamesTech;


use App\Components\Transactions\Strategies\EuroGamesTech\ProcessEuroGamesTech;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;

class EgtHelper
{
    const DEFENCE_CODE_EXPIRATION_TIME = 120;

    public static function generateDefenceCode(int $userId, string $currency, $time = null){

        if (!$time) {
            $time = time();
        }

        return md5($userId . $currency . config('integrations.egt.secret') . $time) . '-' . $time;
    }

    public static function isDefenceCodeUsed($code)
    {
        return self::cache()->get($code);
    }

    public static function setDefenceCodeUsed($code)
    {
        self::cache()->add($code, true, self::DEFENCE_CODE_EXPIRATION_TIME);
    }

    public static function getCurrencyFromPortalCode(string $portalCode){
        return substr($portalCode, -3);
    }

    public static function getTransactionType(string $reason, bool $deposit = false)
    {
        $map = [
            'ROUND_BEGIN'  => TransactionRequest::TRANS_BET,
            'ROUND_END'    => TransactionRequest::TRANS_WIN,
            'ROUND_CANCEL' => TransactionRequest::TRANS_REFUND,
            'JACKPOT_END'  => TransactionRequest::TRANS_BONUS_WIN
        ];

        return array_get($map, $reason, $deposit ? TransactionRequest::TRANS_WIN : TransactionRequest::TRANS_BET);
    }


    /**
     * @param TransactionRequest $transactionRequest
     * @param IntegrationUser $user
     * @return \App\Components\Transactions\TransactionResponse
     */
    public static function handleTransaction($transactionRequest, $user){

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessEuroGamesTech());

        if($transactionResponse->isDuplicate()){
            throw new ApiHttpException(409, null, array_merge(CodeMapping::getByMeaning(CodeMapping::DUPLICATE),[
                'Balance' => $transactionResponse->getBalance() * 100,
                'CasinoTransferId' => $transactionResponse->operation_id
            ]));
        }

        if($transactionResponse->operation_id === null){
            throw new ApiHttpException(504, null, CodeMapping::getByMeaning(CodeMapping::TIMED_OUT));
        }

        return $transactionResponse;
    }

    /**
     * @param string $userCurrency
     * @param string $inputCurrency
     */
    public static function checkInputCurrency(string $userCurrency, string $inputCurrency){
        if($userCurrency != $inputCurrency){
            throw new ApiHttpException(409, "Currency mismatch", CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY));
        }
    }

    /**
     * @return \Illuminate\Cache\Repository
     */
    private static function cache()
    {
        return app('cache')->store('redis_egt');
    }
}