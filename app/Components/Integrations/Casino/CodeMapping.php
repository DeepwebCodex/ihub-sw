<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations\Casino;


use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    public static function getMapping(){
        return [
            StatusCode::SERVER_ERROR => [
                'message'   => 'Server error',
                'map'       => [],
                'default'   => true,
                'attribute' => null,
                'meanings'  => [self::SERVER_ERROR]
            ],
            StatusCode::SUCCESS => [
                'message'   => 'success',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::NO_MONEY => [
                'message'   => 'Not enough money',
                'map'       => [1027, 1409],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
            StatusCode::CURRENCY_MISMATCH => [
                'message'   => 'Currency mismatch',
                'map'       => [1401],
                'attribute' => null,
                'meanings'  => [self::INVALID_CURRENCY]
            ],
            StatusCode::USER_NOT_FOUND => [
                'message'   => 'User not found',
                'map'       => [1024, 1410],
                'attribute' => null,
                'meanings'  => [self::USER_NOT_FOUND]
            ],
            StatusCode::INCORRECT_RESPONSE => [
                'message'   => 'Incorrect response',
                'map'       => [1020, -2],
                'attribute' => null,
                'meanings'  => [self::INVALID_RESPONSE]
            ],
            StatusCode::INVALID_TOKEN => [
                'message'   => 'Invalid token',
                'map'       => [88618],
                'attribute' => 'token',
                'meanings'  => [self::INVALID_TOKEN]
            ],
            StatusCode::INCORRECT_USER_ID => [
                'message'   => 'Incorrect user Id',
                'map'       => [],
                'attribute' => 'user_id',
                'meanings'  => [self::INVALID_USER_ID]
            ],
            StatusCode::INCORRECT_RESULT => [
                'message'   => 'Incorrect result',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_RESULT]
            ],
            StatusCode::UNKNOWN_IMPRINT => [
                'message'   => 'Unknown imprint',
                'map'       => [5404],
                'attribute' => null,
                'meanings'  => [self::UNKNOWN_METHOD]
            ],
            StatusCode::TIME_EXPIRED => [
                'message'   => 'Time Expired',
                'map'       => [],
                'attribute' => 'time',
                'meanings'  => [self::TIME_EXPIRED]
            ],
            StatusCode::INVALID_SIGNATURE => [
                'message'   => 'Invalid signature',
                'map'       => [],
                'attribute' => 'signature',
                'meanings'  => [self::INVALID_SIGNATURE]
            ],
            StatusCode::WRONG_AMOUNT => [
                'message' => 'Amount is less or equal zero',
                'map'     => [],
                'attribute' => 'amount',
                'meanings'  => [self::INVALID_SUM]
            ],
            StatusCode::INVALID_SERVICE => [
                'message' => 'Invalid Service',
                'map'     => [5001],
                'attribute' => null,
                'meanings'  => [self::INVALID_SERVICE]
            ],
            StatusCode::INVALID_WALLET => [
                'message' => 'Invalid wallet',
                'map'     => [5002],
                'attribute' => null,
                'meanings'  => [self::INVALID_WALLET]
            ]
        ];
    }
}