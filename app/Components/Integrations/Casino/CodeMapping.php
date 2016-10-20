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
            0 => [
                'message'   => 'Server error',
                'map'       => [],
                'default'   => true,
                'attribute' => null,
                'meanings'  => [self::SERVER_ERROR]
            ],
            1 => [
                'message'   => 'success',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            2 => [
                'message'   => 'Not enough money',
                'map'       => [1027, 1409],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
            3 => [
                'message'   => 'Currency mismatch',
                'map'       => [1401],
                'attribute' => null,
                'meanings'  => [self::INVALID_CURRENCY]
            ],
            4 => [
                'message'   => 'User not found',
                'map'       => [1024, 1410],
                'attribute' => null,
                'meanings'  => [self::USER_NOT_FOUND]
            ],
            5 => [
                'message'   => 'Incorrect response',
                'map'       => [1020, -2],
                'attribute' => null,
                'meanings'  => [self::INVALID_RESPONSE]
            ],
            6 => [
                'message'   => 'Invalid token',
                'map'       => [],
                'attribute' => 'token',
                'meanings'  => [self::INVALID_TOKEN]
            ],
            7 => [
                'message'   => 'Incorrect user Id',
                'map'       => [],
                'attribute' => 'user_id',
                'meanings'  => [self::INVALID_USER_ID]
            ],
            8 => [
                'message'   => 'Incorrect result',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_RESULT]
            ],
            9 => [
                'message'   => 'Unknown imprint',
                'map'       => [5404],
                'attribute' => null,
                'meanings'  => [self::UNKNOWN_METHOD]
            ],
            10 => [
                'message'   => 'Time Expired',
                'map'       => [],
                'attribute' => 'time',
                'meanings'  => [self::TIME_EXPIRED]
            ],
            11 => [
                'message'   => 'Invalid signature',
                'map'       => [],
                'attribute' => 'signature',
                'meanings'  => [self::INVALID_SIGNATURE]
            ],
            12 => [
                'message' => 'Amount is less or equal zero',
                'map'     => [],
                'attribute' => 'amount',
                'meanings'  => [self::INVALID_SUM]
            ],
            13 => [
                'message' => 'Invalid Service',
                'map'     => [5001],
                'attribute' => null,
                'meanings'  => [self::INVALID_SERVICE]
            ],
            14 => [
                'message' => 'Invalid wallet',
                'map'     => [5002],
                'attribute' => null,
                'meanings'  => [self::INVALID_WALLET]
            ]
        ];
    }
}