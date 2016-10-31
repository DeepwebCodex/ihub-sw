<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations\EuroGamesTech;
use App\Components\Integrations\EuroGamesTech\StatusCode;


use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    public static function getMapping(){
        return [
            StatusCode::OK => [
                'message'   => 'OK',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::DUPLICATE => [
                'message'   => 'Transaction duplicate',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::DUPLICATE]
            ],
            StatusCode::OK_DO_REALITY_CHECK => [
                'message'   => 'Success (player protection)',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::OK_90_TIME_PROXIMITY_ALERT => [
                'message'   => 'Success - time is almost out (player protection)',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::OK_10_PERCENT_CREDIT_LEFT_ALERT => [
                'message'   => 'Success - low funds (player protection)',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::TIMED_OUT => [
                'message'   => 'Timed out',
                'map'       => [-1],
                'attribute' => null,
                'meanings'  => [self::TIMED_OUT]
            ],
            StatusCode::INTERNAL_SERVER_ERROR => [
                'message'   => 'Server error',
                'map'       => [],
                'attribute' => null,
                'default'   => true,
                'meanings'  => [self::SERVER_ERROR, self::USER_NOT_FOUND]
            ],
            StatusCode::INSUFFICIENT_FUNDS => [
                'message'   => 'Insufficient funds',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
            StatusCode::BET_LIMIT_REACHED => [
                'message'   => 'Bet limit reached',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::BET_LIMIT]
            ],
            StatusCode::LOSS_LIMIT_REACHED => [
                'message'   => 'Loss limit reached',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ],
            StatusCode::SESSION_TIME_LIMIT_REACHED => [
                'message'   => 'Session time limit reached',
                'map'       => [],
                'attribute' => null,
                'meanings'  => []
            ]
        ];
    }
}