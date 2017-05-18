<?php

namespace App\Components\Integrations\DriveMedia\Playtech;

use App\Components\Integrations\DriveMedia\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Support\Facades\Config;

/**
 * Class PlaytechHelper
 * @package App\Components\Integrations\DriveMedia\Playtech
 */
class PlaytechHelper
{
    private static $map = [
        'getBalance' => 'balance',
        'writeBet' => 'bet',
    ];

    /**
     * @param string $methodName
     * @return mixed
     */
    public static function mapMethod(string $methodName)
    {
        return array_get(self::$map, $methodName, $methodName);
    }

    /**
     * @param float $bet
     * @param float $win_lose
     * @param string $bet_info
     * @param array $transactions
     * @return array
     */
    public static function getTransactions(float $bet, float $win_lose, string $bet_info, $transactions = []):array
    {
        if ($bet != 0) {
            if ($win_lose >= 0) {
                array_push($transactions, [
                    'amount' => $bet,
                    'type' => TransactionRequest::TRANS_BET
                ]);
                array_push($transactions, [
                    'amount' => $win_lose + $bet,
                    'type' => TransactionRequest::TRANS_WIN
                ]);
            } else {
                if ($bet == ($win_lose * -1)) {
                    array_push($transactions, [
                        'amount' => $bet,
                        'type' => TransactionRequest::TRANS_BET
                    ]);
                } else {
                    array_push($transactions, [
                        'amount' => $bet,
                        'type' => TransactionRequest::TRANS_BET
                    ]);
                    array_push($transactions, [
                        'amount' => $win_lose + $bet,
                        'type' => TransactionRequest::TRANS_WIN
                    ]);
                }
            }
        } else {
            $type = TransactionRequest::TRANS_WIN;
            if($bet_info == 'bonus')
            {
                $type = TransactionRequest::TRANS_BONUS_WIN;
            }
            array_push($transactions, [
                'amount' => $win_lose,
                'type' => $type
            ]);
        }

        return $transactions;
    }

    /**
     * @param array $query
     * @return array
     */
    public static function clearRequest(array $query):array
    {
        $params = [
            'sign',
            'partnerId',
            'cashdeskId',
            'userIp',
            'userId'
        ];

        foreach ($params as $key) {
            unset($query[$key]);
        }

        return $query;
    }

    /**
     * @param $space
     * @return mixed
     */
    public static function getKey($space)
    {
        $spaces = Config::get("integrations.DriveMediaPlaytech.spaces");
        foreach ($spaces as $k => $v) {
            if($v['id'] === $space) {
                return $v['key'];
            }
        }

        throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @param $space
     * @return bool
     */
    public static function getSpace($space):bool
    {
        $spaces = Config::get("integrations.DriveMediaPlaytech.spaces");
        foreach ($spaces as $k => $v) {
            if($v['id'] === $space) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $userCurrency
     * @param $reqSpace
     */
    public static function checkCurrency($userCurrency, $reqSpace)
    {
        $space = Config::get("integrations.DriveMediaPlaytech.spaces.{$userCurrency}.id");

        if($reqSpace != $space) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        }
    }
}