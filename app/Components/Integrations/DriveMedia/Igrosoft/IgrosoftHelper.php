<?php

namespace App\Components\Integrations\DriveMedia\Igrosoft;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Config;

class IgrosoftHelper
{
    private static $map = [
        'getBalance' => 'balance',
        'writeBet' => 'bet',
    ];

    private static $types = [
        'Bonus',
        'Gamble',
        'Start_Gamble',
        'Super_Bonus'
    ];

    public static function mapMethod(string $methodName)
    {
        return array_get(self::$map, $methodName, $methodName);
    }

    public static function getTransactions(float $bet, float $win_lose, string $bet_info, $transactions = []):array
    {
        if ($bet != 0) {
            if ($win_lose >= 0) {
                array_push($transactions, [
                    'amount' => $bet,
                    'type' => TransactionRequest::TRANS_BET
                ]);

                array_push($transactions, [
                    'amount' => $win_lose,
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
            if(in_array($bet_info, self::$types))
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
        $spaces = Config::get("integrations.DriveMediaIgrosoft.spaces");
        foreach ($spaces as $k => $v) {
            if($v['space'] === $space) {
                return $v['key'];
            }
        }

        throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @param $userCurrency
     * @param $reqSpace
     */
    public static function checkCurrency($userCurrency, $reqSpace)
    {
        $space = Config::get("integrations.DriveMediaIgrosoft.spaces.{$userCurrency}.space");

        if($reqSpace != $space) {
            throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
        }
    }
}